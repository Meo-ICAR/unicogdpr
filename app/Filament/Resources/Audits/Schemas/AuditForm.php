<?php

namespace App\Filament\Resources\Audits\Schemas;

use App\Enums\AuditStatus;
use App\Models\ClientController;
use App\Models\Company;
use App\Models\Employee;
use App\Models\ExternalProcessor;
use App\Models\Fornitore;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AuditForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Soggetto Controllato')
                    ->icon('heroicon-o-magnifying-glass')
                    ->schema([
                        MorphToSelect::make('auditable')
                            ->label('Soggetto Sottoposto ad Audit')
                            ->types([
                                MorphToSelect\Type::make(Company::class)->titleAttribute('name')->label('Azienda'),
                                MorphToSelect\Type::make(Employee::class)->titleAttribute('first_name')->label('Dipendente'),
                                MorphToSelect\Type::make(Fornitore::class)->titleAttribute('name')->label('Fornitore'),
                                MorphToSelect\Type::make(ExternalProcessor::class)->titleAttribute('name')->label('Responsabile Esterno'),
                                MorphToSelect\Type::make(ClientController::class)->titleAttribute('name')->label('Cliente / Committente'),
                            ])
                            ->searchable()
                            ->preload(),
                    ]),

                Section::make('Dati Audit')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->columns(2)
                    ->schema([
                        TextInput::make('protocol_number')
                            ->label('Numero Protocollo')
                            ->maxLength(255),
                        TextInput::make('auditor_name')
                            ->label('Auditor')
                            ->maxLength(255),
                        DatePicker::make('scheduled_at')
                            ->label('Data Pianificata'),
                        DatePicker::make('executed_at')
                            ->label('Data Esecuzione'),
                        Select::make('status')
                            ->label('Stato')
                            ->options(AuditStatus::options())
                            ->default(AuditStatus::Scheduled->value)
                            ->required(),
                        DatePicker::make('followup_date')
                            ->label('Data Follow-up'),
                        Textarea::make('scope')
                            ->label('Ambito / Scope')
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('summary')
                            ->label('Sintesi Esito')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('remediation_plan')
                            ->label('Piano di Rimedio')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
