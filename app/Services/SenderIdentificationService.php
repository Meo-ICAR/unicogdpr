<?php

namespace App\Services;

use App\Models\ClientController;
use App\Models\ComplaintRegistry;
use App\Models\DataSubjectRequest;
use App\Models\Employee;
use App\Models\ExternalProcessor;
use Illuminate\Support\Collection;

/**
 * Identifica i possibili referenti collegati a un indirizzo email e/o numero
 * di telefono in ricezione/invio, interrogando i campi già esistenti sui
 * modelli GDPR (nessuna tabella o morph nuovi). Campi come dpo_contact
 * possono contenere piu' indirizzi separati da "/" o ",": vengono estratti
 * singolarmente per evitare falsi positivi da un semplice LIKE.
 */
class SenderIdentificationService
{
    private const EMAIL_PATTERN = '/[A-Za-z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?(?:\.[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?)+/';

    /**
     * @return Collection<int, array{type: string, label: string, matched_on: string, model: mixed}>
     */
    public function identify(?string $email = null, ?string $phone = null, ?string $companyId = null): Collection
    {
        $email = filled($email) ? mb_strtolower(trim($email)) : null;
        $phone = filled($phone) ? preg_replace('/\D+/', '', $phone) : null;

        if (blank($email) && blank($phone)) {
            return collect();
        }

        return collect()
            ->merge($email ? $this->matchExternalProcessors($email, $companyId) : [])
            ->merge($email ? $this->matchClientControllers($email, $companyId) : [])
            ->merge($email ? $this->matchEmployees($email, $companyId) : [])
            ->merge($this->matchDataSubjectRequests($email, $phone, $companyId))
            ->merge($this->matchComplaintRegistryEntries($email, $phone, $companyId))
            ->values();
    }

    /**
     * Estrae tutti gli indirizzi email presenti in un campo, anche quando
     * ne contiene piu' d'uno separati da testo libero (es. "a@x.com /
     * b@y.com" in dpo_contact).
     *
     * @return array<int, string>
     */
    private function extractEmails(?string $field): array
    {
        if (blank($field)) {
            return [];
        }

        preg_match_all(self::EMAIL_PATTERN, $field, $found);

        return array_map('mb_strtolower', $found[0] ?? []);
    }

    private function fieldContainsEmail(?string $field, string $email): bool
    {
        return in_array($email, $this->extractEmails($field), true);
    }

    private function matchExternalProcessors(string $email, ?string $companyId): Collection
    {
        return ExternalProcessor::query()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->where(fn ($q) => $q->where('email', 'like', "%{$email}%")->orWhere('dpo_contact', 'like', "%{$email}%"))
            ->get()
            ->filter(fn (ExternalProcessor $ep) => $this->fieldContainsEmail($ep->email, $email) || $this->fieldContainsEmail($ep->dpo_contact, $email))
            ->map(fn (ExternalProcessor $ep) => [
                'type' => 'external_processor',
                'label' => $ep->name,
                'matched_on' => $this->fieldContainsEmail($ep->email, $email) ? 'email' : 'dpo_contact',
                'model' => $ep,
            ]);
    }

    private function matchClientControllers(string $email, ?string $companyId): Collection
    {
        return ClientController::query()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->where(fn ($q) => $q->where('email', 'like', "%{$email}%")
                ->orWhere('pec', 'like', "%{$email}%")
                ->orWhere('dpo_contact', 'like', "%{$email}%"))
            ->get()
            ->filter(fn (ClientController $cc) => $this->fieldContainsEmail($cc->email, $email)
                || $this->fieldContainsEmail($cc->pec, $email)
                || $this->fieldContainsEmail($cc->dpo_contact, $email))
            ->map(fn (ClientController $cc) => [
                'type' => 'client_controller',
                'label' => $cc->name,
                'matched_on' => match (true) {
                    $this->fieldContainsEmail($cc->email, $email) => 'email',
                    $this->fieldContainsEmail($cc->pec, $email) => 'pec',
                    default => 'dpo_contact',
                },
                'model' => $cc,
            ]);
    }

    private function matchEmployees(string $email, ?string $companyId): Collection
    {
        return Employee::query()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->where(fn ($q) => $q->where('email', 'like', "%{$email}%")->orWhere('pec', 'like', "%{$email}%"))
            ->get()
            ->filter(fn (Employee $employee) => $this->fieldContainsEmail($employee->email, $email) || $this->fieldContainsEmail($employee->pec, $email))
            ->map(fn (Employee $employee) => [
                'type' => 'employee',
                'label' => trim("{$employee->first_name} {$employee->last_name}"),
                'matched_on' => $this->fieldContainsEmail($employee->email, $email) ? 'email' : 'pec',
                'model' => $employee,
            ]);
    }

    private function matchDataSubjectRequests(?string $email, ?string $phone, ?string $companyId): Collection
    {
        if (blank($email) && blank($phone)) {
            return collect();
        }

        return DataSubjectRequest::query()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->where(function ($q) use ($email, $phone) {
                if (filled($email)) {
                    $q->orWhere('requester_email', $email);
                }
                if (filled($phone)) {
                    $q->orWhereRaw("REPLACE(REPLACE(REPLACE(requester_phone, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$phone}"]);
                }
            })
            ->get()
            ->map(fn (DataSubjectRequest $dsar) => [
                'type' => 'data_subject_request',
                'label' => $dsar->requester_name,
                'matched_on' => $dsar->requester_email === $email ? 'requester_email' : 'requester_phone',
                'model' => $dsar,
            ]);
    }

    private function matchComplaintRegistryEntries(?string $email, ?string $phone, ?string $companyId): Collection
    {
        if (blank($email) && blank($phone)) {
            return collect();
        }

        return ComplaintRegistry::query()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->where(function ($q) use ($email, $phone) {
                if (filled($email)) {
                    $q->orWhere('complainant_email', $email)->orWhere('receiving_email', $email);
                }
                if (filled($phone)) {
                    $q->orWhereRaw("REPLACE(REPLACE(REPLACE(complainant_phone, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$phone}"]);
                }
            })
            ->get()
            ->unique('protocol_number')
            ->map(fn (ComplaintRegistry $complaint) => [
                'type' => 'complaint_registry',
                'label' => $complaint->complainant_name ?: $complaint->protocol_number,
                'matched_on' => $complaint->complainant_email === $email ? 'complainant_email' : ($complaint->receiving_email === $email ? 'receiving_email' : 'complainant_phone'),
                'model' => $complaint,
            ]);
    }
}
