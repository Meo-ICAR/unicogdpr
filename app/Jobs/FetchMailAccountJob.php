<?php

namespace App\Jobs;

use App\Contracts\ImapConnector;
use App\Enums\ComplaintStatus;
use App\Enums\EmailClassification;
use App\Enums\ReceptionChannel;
use App\Models\ComplaintRegistry;
use App\Models\DataSubjectRequest;
use App\Models\IncomingEmail;
use App\Models\MailAccount;
use App\Services\Drive\ComplaintEmailDriveArchiver;
use App\Services\Mail\EmailClassifier;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Webklex\PHPIMAP\Attribute;

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

        // Non ci si affida al flag "non letta": una mail già aperta altrove
        // (webmail, altro client) verrebbe ignorata per sempre. Si scansiona
        // una finestra di date e il dedup per message_id evita i doppioni.
        $since = $account->last_synced_at
            ? $account->last_synced_at->copy()->subDays(config('gdpr.fetch.overlap_days', 2))
            : now()->subDays(config('gdpr.fetch.initial_days', 30));

        $messages = $client->getFolder('INBOX')->messages()->since($since)->get();

        $imported = 0;
        $dsarCreated = 0;
        $complaintCreated = 0;

        foreach ($messages as $message) {
            if ($imported >= $this->limit) {
                break;
            }

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
                'from_name' => mb_substr((string) ($message->getFrom()[0]->personal ?: $fromAddress), 0, 255),
                'to' => $this->addresses($message->getTo()),
                'cc' => $this->addresses($message->getCc()),
                'subject' => mb_substr((string) ($message->getSubject() ?: '(Senza Oggetto)'), 0, 255),
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

            if ($this->shouldCreateComplaint($classification)) {
                $complaint = $this->createComplaintFromEmail($email, $account);
                $email->complaint_registry_id = $complaint->id;
                $email->save();

                // A differenza di DataSubjectRequest, ComplaintRegistry non
                // implementa Spatie HasMedia (i suoi allegati passano dal
                // morphMany documents()/Document, non da una media
                // collection propria): gli allegati restano sull'email
                // collegata, non vengono copiati automaticamente qui.

                activity('complaint')
                    ->performedOn($complaint)
                    ->withProperties([
                        'origin' => 'imap_fetch',
                        'mail_account_id' => $account->id,
                        'classification' => $classification->value,
                        'from' => $email->from_email,
                        'data_subject_request_id' => $complaint->data_subject_request_id,
                    ])
                    ->log('Reclamo creato automaticamente da email in arrivo');

                $complaintCreated++;
            }

            $this->archiveOnDrive($email);

            $message->setFlag('Seen');
        }

        $account->forceFill(['last_synced_at' => now()])->save();

        Log::channel('stack')->info('Fetch casella completato', [
            'company_id' => $account->company_id,
            'mail_account_id' => $account->id,
            'imported' => $imported,
            'dsar_created' => $dsarCreated,
            'complaint_created' => $complaintCreated,
        ]);
    }

    /**
     * Se Drive non è utilizzabile l'archiviatore salva in locale; un errore
     * residuo non deve comunque bloccare l'import della casella. Si può
     * ripetere con `php artisan emails:archive-drive {id}`.
     */
    private function archiveOnDrive(IncomingEmail $email): void
    {
        if (! $email->classification?->isGdprRelated()) {
            return;
        }

        try {
            app(ComplaintEmailDriveArchiver::class)->archive($email);
        } catch (\Throwable $e) {
            Log::warning('Archiviazione email su Drive fallita', [
                'incoming_email_id' => $email->id,
                'company_id' => $email->company_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function shouldCreateDsar(EmailClassification $classification): bool
    {
        return config('gdpr.auto_create_dsar', true) && $classification->isDsar();
    }

    private function shouldCreateComplaint(EmailClassification $classification): bool
    {
        return config('gdpr.auto_create_complaint', true) && $classification === EmailClassification::Complaint;
    }

    /**
     * Crea l'evento del reclamo per questa email. Se un'altra email della
     * stessa conversazione è già collegata a un reclamo, questo diventa un
     * nuovo evento sullo stesso protocollo (invece di aprirne uno nuovo) e
     * ne eredita la DSAR collegata, se presente. Altrimenti apre un nuovo
     * protocollo e prova ad abbinarsi a una DSAR già aperta dello stesso
     * reclamante (per email o telefono estratto dal testo).
     */
    private function createComplaintFromEmail(IncomingEmail $email, MailAccount $account): ComplaintRegistry
    {
        $previousComplaint = $email->threadMessages()
            ->whereNotNull('complaint_registry_id')
            ->first()
            ?->complaintRegistry;

        $reception = $account->type === 'pec' ? ReceptionChannel::Pec->value : ReceptionChannel::Email->value;
        $bodyText = $email->body_text ?: strip_tags((string) $email->body_html);

        if ($previousComplaint) {
            return ComplaintRegistry::create([
                'company_id' => $account->company_id,
                'protocol_number' => $previousComplaint->protocol_number,
                'data_subject_request_id' => $previousComplaint->data_subject_request_id,
                'event_sequence' => ComplaintRegistry::where('protocol_number', $previousComplaint->protocol_number)->max('event_sequence') + 1,
                'event_at' => $email->received_at,
                'event_phase' => 'Email in arrivo',
                'received_at' => $previousComplaint->received_at,
                'reception_channel' => $reception,
                'complainant_name' => $email->from_name,
                'complainant_email' => $email->from_email,
                'description' => $bodyText,
                'status' => ComplaintStatus::Received->value,
            ]);
        }

        $phoneCandidate = $this->extractPhoneCandidate($bodyText);
        $matchedDsar = DataSubjectRequest::findOpenForContact($email->from_email, $phoneCandidate);

        return ComplaintRegistry::create([
            'company_id' => $account->company_id,
            'protocol_number' => ComplaintRegistry::generateNextProtocolNumber(),
            'data_subject_request_id' => $matchedDsar?->id,
            'event_sequence' => 1,
            'event_at' => $email->received_at,
            'event_phase' => '1° Email/PEC Reclamo',
            'received_at' => $email->received_at,
            'reception_channel' => $reception,
            'complainant_name' => $email->from_name,
            'complainant_email' => $email->from_email,
            'complainant_phone' => $phoneCandidate,
            'description' => $bodyText,
            'status' => ComplaintStatus::Received->value,
        ]);
    }

    /**
     * Estrae un numero di cellulare italiano plausibile dal testo (best
     * effort): usato solo per provare l'abbinamento a una DSAR aperta dello
     * stesso reclamante, non viene validato oltre il pattern.
     */
    private function extractPhoneCandidate(string $text): ?string
    {
        if (preg_match('/\b3\d{8,9}\b/', preg_replace('/[\s.-]+/', '', $text) ?? '', $matches)) {
            return $matches[0];
        }

        return null;
    }

    private function storeAttachments(object $message, IncomingEmail $email): void
    {
        if (! $message->hasAttachments()) {
            return;
        }

        foreach ($message->getAttachments() as $attachment) {
            $fileName = $this->safeAttachmentName((string) $attachment->getName());
            $tmp = tempnam(sys_get_temp_dir(), 'mail_att_');

            try {
                file_put_contents($tmp, $attachment->getContent());

                $email->addMedia($tmp)
                    ->usingFileName($fileName)
                    ->toMediaCollection('email_attachments');
            } catch (\Throwable $e) {
                // Un allegato non archiviabile (es. oltre il limite di dimensione)
                // non deve bloccare la scansione della casella: l'email resta salvata.
                Log::warning('Allegato email non archiviato', [
                    'incoming_email_id' => $email->id,
                    'file' => $fileName,
                    'error' => $e->getMessage(),
                ]);
            } finally {
                @unlink($tmp);
            }
        }
    }

    /**
     * Decodifica il nome allegato MIME (=?UTF-8?Q?...?=), rimuove i separatori
     * di percorso e lo accorcia mantenendo l'estensione.
     */
    private function safeAttachmentName(string $name): string
    {
        $decoded = trim(str_replace(['/', '\\', "\0"], '-', iconv_mime_decode($name, 0, 'UTF-8') ?: $name));

        if ($decoded === '') {
            return 'allegato';
        }

        $extension = pathinfo($decoded, PATHINFO_EXTENSION);
        $extension = mb_strlen($extension) <= 10 ? $extension : '';
        $base = mb_substr(pathinfo($decoded, PATHINFO_FILENAME), 0, 120);

        return $extension !== '' ? $base.'.'.$extension : $base;
    }

    /**
     * @param  Attribute|array<int, object>|null  $addresses  L'header To/Cc di
     *                                                        Webklex\PHPIMAP è sempre un Attribute che racchiude uno o più indirizzi.
     * @return array<int, array{email:?string,name:?string}>
     */
    private function addresses($addresses): array
    {
        if ($addresses instanceof Attribute) {
            $addresses = $addresses->all();
        } elseif (! is_array($addresses)) {
            $addresses = $addresses ? iterator_to_array($addresses) : [];
        }

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
