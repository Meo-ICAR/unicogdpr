<?php

namespace App\Jobs;

use App\Contracts\ImapConnector;
use App\Enums\EmailClassification;
use App\Models\DataSubjectRequest;
use App\Models\IncomingEmail;
use App\Models\MailAccount;
use App\Services\Mail\EmailClassifier;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Scansiona una singola casella IMAP: archivia le email in incoming_emails,
 * salva gli allegati, classifica e (se abilitato) apre le DSAR.
 */
class FetchMailAccountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public function __construct(
        public MailAccount $account,
        public int $limit = 50,
    ) {
        $this->onQueue(config('gdpr.fetch.queue', 'mail'));
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [(new WithoutOverlapping('mail-account:'.$this->account->getKey()))->dontRelease()];
    }

    public function handle(ImapConnector $connections, EmailClassifier $classifier): void
    {
        $account = $this->account;

        $client = $connections->make($account);
        $client->connect();

        $messages = $client->getFolder('INBOX')->messages()->unseen()->get()->take($this->limit);

        $imported = 0;
        $dsarCreated = 0;

        foreach ($messages as $message) {
            $fromAddress = $message->getFrom()[0]->mail ?? null;

            if (! $fromAddress) {
                continue;
            }

            $messageId = $this->normalizeId((string) $message->getMessageId())
                ?: 'fallback-'.hash('sha256', $fromAddress.'|'.$message->getSubject().'|'.$this->messageDate($message)->toIso8601String());

            $existing = IncomingEmail::withTrashed()
                ->where('company_id', $account->company_id)
                ->where('message_id', $messageId)
                ->first();

            if ($existing) {
                $message->setFlag('Seen');

                continue;
            }

            $references = implode(' ', array_map(
                fn ($r) => $this->normalizeId((string) $r),
                (array) $message->getReferences()->toArray()
            ));
            $inReplyTo = $this->normalizeId((string) $message->getInReplyTo());

            $email = new IncomingEmail([
                'company_id' => $account->company_id,
                'mail_account_id' => $account->id,
                'message_id' => $messageId,
                'in_reply_to' => $inReplyTo ?: null,
                'references' => $references ?: null,
                'thread_id' => IncomingEmail::deriveThreadId($references, $inReplyTo, $messageId),
                'from_email' => $fromAddress,
                'from_name' => $message->getFrom()[0]->personal ?? $fromAddress,
                'to' => $this->addresses($message->getTo()),
                'cc' => $this->addresses($message->getCc()),
                'subject' => $message->getSubject() ?: '(Senza Oggetto)',
                'body_text' => $message->getTextBody() ?: null,
                'body_html' => $message->getHTMLBody() ?: null,
                'received_at' => $this->messageDate($message),
                'is_read' => false,
            ]);

            $classification = $classifier->classify($email);
            $email->classification = $classification;
            $email->save();

            $this->storeAttachments($message, $email);

            $imported++;

            if ($this->shouldCreateDsar($classification)) {
                $dsar = DataSubjectRequest::createRequest([
                    'company_id' => $account->company_id,
                    'requester_name' => $email->from_name,
                    'requester_email' => $email->from_email,
                    'request_type' => $classification->toDsarRequestType(),
                    'request_description' => $email->body_text ?: strip_tags((string) $email->body_html),
                    'channel' => $account->type === 'pec' ? 'pec' : 'email',
                    'source_message_id' => $messageId,
                ]);

                $email->dataSubjectRequest()->associate($dsar)->save();

                foreach ($email->getMedia('email_attachments') as $media) {
                    $media->copy($dsar, 'dsar_attachments');
                }

                activity('dsar')
                    ->performedOn($dsar)
                    ->withProperties([
                        'origin' => 'imap_fetch',
                        'mail_account_id' => $account->id,
                        'classification' => $classification->value,
                        'from' => $email->from_email,
                    ])
                    ->log('DSAR creata automaticamente da email in arrivo');

                $dsarCreated++;
            }

            $message->setFlag('Seen');
        }

        $account->forceFill(['last_synced_at' => now()])->save();

        Log::channel('stack')->info('Fetch casella completato', [
            'company_id' => $account->company_id,
            'mail_account_id' => $account->id,
            'imported' => $imported,
            'dsar_created' => $dsarCreated,
        ]);
    }

    private function shouldCreateDsar(EmailClassification $classification): bool
    {
        return config('gdpr.auto_create_dsar', true) && $classification->isDsar();
    }

    private function storeAttachments(object $message, IncomingEmail $email): void
    {
        if (! $message->hasAttachments()) {
            return;
        }

        foreach ($message->getAttachments() as $attachment) {
            $tmp = sys_get_temp_dir().'/'.uniqid('mail_att_').'_'.$attachment->getName();
            file_put_contents($tmp, $attachment->getContent());

            $email->addMedia($tmp)
                ->usingFileName($attachment->getName())
                ->toMediaCollection('email_attachments');

            @unlink($tmp);
        }
    }

    /**
     * @param  array<int, object>  $addresses
     * @return array<int, array{email:?string,name:?string}>
     */
    private function addresses(array $addresses): array
    {
        return array_values(array_map(fn ($a) => [
            'email' => $a->mail ?? null,
            'name' => $a->personal ?? null,
        ], $addresses));
    }

    private function messageDate(object $message): Carbon
    {
        try {
            return Carbon::parse((string) $message->getDate());
        } catch (\Throwable) {
            return now();
        }
    }

    private function normalizeId(string $value): string
    {
        return mb_substr(trim($value, " \t\n\r\0\x0B<>"), 0, 255);
    }
}
