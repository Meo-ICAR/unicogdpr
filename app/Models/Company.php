<?php

namespace App\Models;

use App\Models\Concerns\UsesDefaultConnection;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Company extends Model implements HasAvatar
{
    use HasFactory, HasUuids, UsesDefaultConnection;

    /**
     * docnumber convenzionale usato per riconoscere, tra i documents di una
     * company, quello che rappresenta il logo aziendale mostrato nello
     * switcher tenant di Filament (vedi getFilamentAvatarUrl()).
     */
    public const LOGO_DOCNUMBER = 'LOGO';

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
        'is_active',
        'drive_folder_id',
        'vat_number',
        'tax_code',
        'address',
        'phone',
        'startup_cost',
        'advance_percentage',
        'balance_percentage',
        'monthly_cost',
        'billing_frequency',
        'payment_start_date',
        'notes',
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
        'is_active' => 'boolean',
        'startup_cost' => 'decimal:2',
        'advance_percentage' => 'decimal:2',
        'balance_percentage' => 'decimal:2',
        'monthly_cost' => 'decimal:2',
        'payment_start_date' => 'date',
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

    /**
     * Il Document (con relativo media allegato) che rappresenta il logo
     * aziendale, se presente — riconosciuto per convenzione tramite
     * docnumber = self::LOGO_DOCNUMBER, non un campo/collection dedicati.
     */
    public function logoDocument(): ?Document
    {
        return $this->documents()->where('docnumber', self::LOGO_DOCNUMBER)->latest()->first();
    }

    /**
     * Mostra il logo aziendale (se caricato) nello switcher tenant di
     * Filament. Il file resta sul disco privato del Document (nessuna
     * eccezione "pubblica" per il logo): viene servito tramite una route
     * autenticata dedicata, come già avviene per gli altri download di
     * Document.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        $document = $this->logoDocument();

        if (! $document || ! $document->getFirstMedia('documents')) {
            return null;
        }

        return route('company.logo', $this);
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
