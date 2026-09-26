<?php

namespace App\Models;

use App\Models\Concerns\UsesDefaultConnection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Company extends Model
{
    use HasFactory, HasUuids, UsesDefaultConnection;

    protected static function booted(): void
    {
        // Colonna qualificata: questo global scope si applica anche quando
        // Company viene raggiunta da una relazione annidata (es. tenant
        // scoping automatico di Filament su DpiaItem->company() attraverso
        // Dpia), dove un `orderBy('name')` non qualificato diventa ambiguo
        // se la tabella joinata ha anch'essa una colonna `name` (es. dpias).
        static::addGlobalScope('alphabetical', fn (Builder $query) => $query->orderBy('companies.name'));
    }

    protected $fillable = [
        'name',
        'vat_number',
        'tax_code',
        'address',
        'phone',
        'property_name',
        'property_email',
        'referee',
        'email_referee',
        'it_name',
        'email_it',
        'holding_id',
        'email',
        'imap_host',
        'imap_port',
        'imap_encryption',
        'imap_username',
        'imap_password',
        'imap_is_active',
        'pec',
        'pec_imap_host',
        'pec_imap_port',
        'pec_imap_encryption',
        'pec_imap_username',
        'pec_imap_password',
        'pec_imap_is_active',
    ];

    protected $casts = [
        'imap_port' => 'integer',
        'imap_is_active' => 'boolean',
        'imap_password' => 'encrypted',
        'pec_imap_port' => 'integer',
        'pec_imap_is_active' => 'boolean',
        'pec_imap_password' => 'encrypted',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Mandanti/Committenti per cui questa azienda opera come Responsabile
     * del trattamento (es. ECOM per PALK).
     */
    public function clientControllers(): HasMany
    {
        return $this->hasMany(ClientController::class);
    }

    public function holding(): BelongsTo
    {
        return $this->belongsTo(Holding::class);
    }

    public function mailAccounts(): HasMany
    {
        return $this->hasMany(MailAccount::class);
    }

    public function incomingEmails(): HasMany
    {
        return $this->hasMany(IncomingEmail::class);
    }

    public function dataSubjectRequests(): HasMany
    {
        return $this->hasMany(DataSubjectRequest::class);
    }

    public function emailBounces(): HasMany
    {
        return $this->hasMany(EmailBounce::class);
    }

    public function consentLogs(): HasMany
    {
        return $this->hasMany(ConsentLog::class);
    }

    public function leadTransfers(): HasMany
    {
        return $this->hasMany(LeadTransfer::class);
    }

    public function leadReturnLogs(): HasMany
    {
        return $this->hasMany(LeadReturnLog::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function processingActivities(): HasMany
    {
        return $this->hasMany(ProcessingActivity::class);
    }

    public function dpias(): HasMany
    {
        return $this->hasMany(Dpia::class);
    }

    public function dataBreaches(): HasMany
    {
        return $this->hasMany(DataBreach::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function branches(): MorphMany
    {
        return $this->morphMany(Branch::class, 'branchable');
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(ComplaintRegistry::class);
    }

    public function audits(): MorphMany
    {
        return $this->morphMany(Audit::class, 'auditable');
    }

    public function websites(): MorphMany
    {
        return $this->morphMany(Website::class, 'websiteable');
    }
}
