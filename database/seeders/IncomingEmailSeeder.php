<?php

namespace Database\Seeders;

use App\Enums\EmailClassification;
use App\Models\Company;
use App\Models\DataSubjectRequest;
use App\Models\IncomingEmail;
use App\Models\MailAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IncomingEmailSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('incoming_emails')->truncate();
        Schema::enableForeignKeyConstraints();

        foreach (Company::query()->limit(2)->get() as $company) {
            $account = MailAccount::where('company_id', $company->id)->where('type', 'email')->first();

            $samples = [
                [
                    'from_email' => 'interessato.accesso@example.com',
                    'from_name' => 'Marco Interessato',
                    'subject' => 'Richiesta di accesso ai miei dati personali (art. 15 GDPR)',
                    'body_text' => 'Buongiorno, con la presente esercito il diritto di accesso ai miei dati personali ai sensi dell\'art. 15 del Regolamento UE 2016/679. Chiedo copia dei dati trattati e delle finalità.',
                    'classification' => EmailClassification::DsarAccess,
                    'is_read' => false,
                    'make_dsar' => true,
                ],
                [
                    'from_email' => 'reclami@example.org',
                    'from_name' => 'Studio Legale Bianchi',
                    'subject' => 'Reclamo formale e preavviso di segnalazione al Garante',
                    'body_text' => 'Per conto del nostro assistito presentiamo formale reclamo per il trattamento illecito dei dati e preannunciamo segnalazione all\'Autorità Garante.',
                    'classification' => EmailClassification::Complaint,
                    'is_read' => false,
                    'make_dsar' => false,
                ],
                [
                    'from_email' => 'fornitore@example.com',
                    'from_name' => 'ACME Forniture',
                    'subject' => 'Preventivo materiale di consumo Q4',
                    'body_text' => 'In allegato il preventivo richiesto per la fornitura del quarto trimestre.',
                    'classification' => EmailClassification::Other,
                    'is_read' => true,
                    'make_dsar' => false,
                ],
                [
                    'from_email' => 'mailer-daemon@example.com',
                    'from_name' => 'Mail Delivery System',
                    'subject' => 'Delivery Status Notification (Failure)',
                    'body_text' => 'Your message to utente@dominio-inesistente.tld could not be delivered. 550 5.1.1 user unknown.',
                    'classification' => EmailClassification::Bounce,
                    'is_read' => true,
                    'make_dsar' => false,
                ],
            ];

            foreach ($samples as $i => $sample) {
                $messageId = "seed-{$company->id}-{$i}@example.com";

                $dsarId = null;
                if ($sample['make_dsar']) {
                    $dsarId = DataSubjectRequest::createRequest([
                        'company_id' => $company->id,
                        'requester_name' => $sample['from_name'],
                        'requester_email' => $sample['from_email'],
                        'request_type' => 'access',
                        'request_description' => $sample['body_text'],
                        'channel' => 'email',
                        'source_message_id' => $messageId,
                    ])->id;
                }

                IncomingEmail::create([
                    'company_id' => $company->id,
                    'mail_account_id' => $account?->id,
                    'message_id' => $messageId,
                    'thread_id' => $messageId,
                    'from_email' => $sample['from_email'],
                    'from_name' => $sample['from_name'],
                    'to' => [['email' => $account?->email_address ?? 'dpo@example.it', 'name' => 'DPO']],
                    'subject' => $sample['subject'],
                    'body_text' => $sample['body_text'],
                    'received_at' => now()->subDays(4 - $i)->subHours($i),
                    'is_read' => $sample['is_read'],
                    'classification' => $sample['classification'],
                    'data_subject_request_id' => $dsarId,
                ]);
            }
        }

        $this->command->info(IncomingEmail::count().' email in arrivo seeded.');
    }
}
