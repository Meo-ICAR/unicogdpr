<?php

namespace App\Filament\Resources\ExternalProcessors\RelationManagers;

use App\Filament\Concerns\HasNominaIncaricatoBulkActions;
use App\Models\Company;
use App\Models\Employee;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AuthorizedEmployeesRelationManager extends RelationManager
{
    use HasNominaIncaricatoBulkActions;

    protected static string $relationship = 'authorizedEmployees';

    /**
     * Rendering eager invece di lazy (x-load): la tab si popola col resto
     * della pagina invece di innescare una seconda richiesta Livewire al
     * montaggio, che in questo ambiente non arrivava mai a completarsi.
     */
    protected static bool $isLazy = false;

    protected static ?string $title = 'Operatori / Dipendenti Autorizzati sui sistemi del Titolare';

    /**
     * Il fornitore (es. People Group) ha una propria Company distinta dal
     * Titolare: external_processors.company_id punta al Titolare (PALK), non
     * al fornitore stesso, quindi la Company del fornitore si ricava per
     * nome. Nessun legame diretto esiste oggi nello schema dati (niente P.IVA
     * su external_processors, nessuna FK): il nome dell'ExternalProcessor
     * ("People Group") è solo una forma abbreviata della ragione sociale
     * della Company ("PEOPLE GROUP S.R.L."), quindi serve un confronto
     * "contiene" case-insensitive invece dell'uguaglianza esatta.
     */
    protected function getSupplierCompany(): ?Company
    {
        return Company::withoutGlobalScopes()
            ->whereRaw('UPPER(name) LIKE ?', ['%'.mb_strtoupper($this->getOwnerRecord()->name).'%'])
            ->first();
    }

    /**
     * EmployeeResource è scoped-per-tenant di default: Filament registra un
     * global scope PERMANENTE sul model Employee (non solo sulle query della
     * Resource) che filtra ogni query per company_id = tenant attivo (PALK),
     * per tutta la request. Questo rompe silenziosamente questa tabella,
     * perché i dipendenti del fornitore (People Group) hanno un company_id
     * diverso dal Titolare: qui li vogliamo TUTTI, non solo quelli di PALK.
     * Rimuoviamo lo scope solo su questa query, senza toccare EmployeeResource.
     */
    protected function withoutTenantScope(Builder $query): Builder
    {
        $scopeName = Filament::getCurrentPanel()?->getTenancyScopeName();

        return $scopeName ? $query->withoutGlobalScope($scopeName) : $query;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('status')
                ->label('Stato Autorizzazione')
                ->options([
                    'pending' => 'In Attesa',
                    'approved' => 'Approvato',
                    'revoked' => 'Revocato',
                ])
                ->required(),

            Toggle::make('nda_signed')
                ->label('Accordo di riservatezza firmato per questo incarico?'),

            DatePicker::make('approved_at')
                ->label('Data Approvazione'),

            Textarea::make('notes')
                ->label('Note (es. ruolo / sistema a cui accede)')
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->modifyQueryUsing(fn (Builder $query) => $this->withoutTenantScope($query))
            ->columns([
                TextColumn::make('full_name')
                    ->label('Dipendente')
                    ->sortable(query: fn (Builder $query, string $direction) => $query
                        ->orderBy('first_name', $direction)
                        ->orderBy('last_name', $direction))
                    ->searchable(query: fn (Builder $query, string $search) => $query
                        ->where(fn (Builder $q) => $q
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$search}%"]))),
                TextColumn::make('job_title')
                    ->label('Mansione')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->sortable()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'revoked',
                    ]),

                IconColumn::make('nda_signed')
                    ->label('Accordo Riservatezza')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('approved_at')
                    ->label('Data Auth')
                    ->date()
                    ->sortable(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Autorizza Dipendente')
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(function (Builder $query) {
                        $query = $this->withoutTenantScope($query);
                        $supplierCompany = $this->getSupplierCompany();

                        return $supplierCompany
                            ? $query->where('company_id', $supplierCompany->id)
                            : $query->whereRaw('1 = 0');
                    })
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('status')
                            ->label('Stato Autorizzazione')
                            ->options([
                                'pending' => 'In Attesa',
                                'approved' => 'Approvato',
                                'revoked' => 'Revocato',
                            ])
                            ->default('approved')
                            ->required(),
                        Toggle::make('nda_signed')
                            ->label('Accordo di riservatezza firmato')
                            ->default(true),
                        DatePicker::make('approved_at')
                            ->label('Data Approvazione')
                            ->default(now()),
                    ]),
                Action::make('authorize_all_employees')
                    ->label('Autorizza Tutti i Dipendenti Attuali')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Autorizza (stato "Approvato") tutti i dipendenti attivi del fornitore non ancora presenti in questo elenco.')
                    ->action(function (): void {
                        $processor = $this->getOwnerRecord();
                        $alreadyLinked = $processor->authorizedEmployees()->pluck('employees.id');
                        $supplierCompany = $this->getSupplierCompany();

                        $toAdd = $supplierCompany
                            ? Employee::withoutGlobalScopes()
                                ->where('company_id', $supplierCompany->id)
                                ->where('is_active', true)
                                ->whereNotIn('id', $alreadyLinked)
                                ->get()
                            : collect();

                        if ($toAdd->isEmpty()) {
                            Notification::make()
                                ->title('Nessun nuovo dipendente da autorizzare')
                                ->body('Tutti i dipendenti attivi risultano già presenti in elenco.')
                                ->warning()
                                ->send();

                            return;
                        }

                        foreach ($toAdd as $employee) {
                            $processor->authorizedEmployees()->syncWithoutDetaching([
                                $employee->id => [
                                    'status' => 'approved',
                                    'approved_at' => now(),
                                ],
                            ]);
                        }

                        Notification::make()
                            ->title("{$toAdd->count()} dipendenti autorizzati")
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make()->label('Revoca / Rimuovi'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    static::generateNominaBulkAction(fn () => $this->getOwnerRecord()->company),
                    static::downloadNominaBulkAction(),
                    static::uploadSignedNominaBulkAction(),
                ]),
            ]);
    }
}
