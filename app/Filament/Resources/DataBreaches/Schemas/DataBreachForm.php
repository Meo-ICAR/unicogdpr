<?php

namespace App\Filament\Resources\DataBreaches\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DataBreachForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dati del Segnalante')
                    ->description('Da compilare a cura di chi scopre l\'evento o del referente aziendale.')
                    ->icon('heroicon-o-user')
                    ->columns(3)
                    ->schema([
                        TextInput::make('reporter_name')
                            ->label('Nome e Cognome')
                            ->maxLength(255),
                        TextInput::make('reporter_role')
                            ->label('Ruolo/Azienda')
                            ->placeholder('es. Dipendente Palk / Operatore esterno')
                            ->maxLength(255),
                        TextInput::make('reporter_contact')
                            ->label('Recapito Telefonico / E-mail')
                            ->maxLength(255),
                    ]),

                Section::make('Informazioni Incidente')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Titolo incidente')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('severity')
                            ->label('Gravità')
                            ->required()
                            ->default('medium')
                            ->options([
                                'low' => '🟢 Bassa',
                                'medium' => '🟡 Media',
                                'high' => '🔴 Alta',
                            ]),
                        Select::make('status')
                            ->label('Stato')
                            ->required()
                            ->default('investigating')
                            ->options([
                                'investigating' => '🔍 In indagine',
                                'contained' => '🛡️ Contenuto',
                                'resolved' => '✅ Risolto',
                                'notified' => '📨 Notificato',
                            ]),
                        DateTimePicker::make('discovered_at')
                            ->label('Data/ora scoperta')
                            ->default(now())
                            ->required(),
                        DateTimePicker::make('occurred_at')
                            ->label('Data/ora stimata dell\'evento'),
                        TextInput::make('affected_system')
                            ->label('Luogo o Sistema coinvolto')
                            ->placeholder('es. Sidial, Mail, Smartphone')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Descrizione dell\'incidente')
                            ->rows(4)
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Dati Coinvolti')
                  //  ->icon('heroicon-o-database')
                    ->columns(2)
                    ->schema([
                        Select::make('nature_of_breach')
                            ->label('Natura della violazione')
                            ->options([
                                'confidentiality' => '🔒 Riservatezza',
                                'integrity' => '⚠️ Integrità',
                                'availability' => '🚫 Disponibilità',
                                'combined' => '🔀 Combinata',
                            ]),
                        TextInput::make('approximate_records_count')
                            ->label('N° record coinvolti (stimato)')
                            ->numeric()
                            ->minValue(0),
                        Radio::make('involved_mandate')
                            ->label('Mandataria coinvolta')
                            ->options([
                                'ECOM' => 'ECOM',
                                'Palk' => 'Palk (Dati propri)',
                                'Altro' => 'Altro',
                            ])
                            ->inline(),
                        CheckboxList::make('affected_data_categories')
                            ->label('Categorie di dati interessati')
                            ->options([
                                'Anagrafici' => 'Anagrafici',
                                'Contatti' => 'Contatti',
                                'Documenti' => 'Documenti',
                                'IBAN' => 'IBAN',
                            ])
                            ->afterStateHydrated(function (CheckboxList $component, $state): void {
                                $component->state(is_string($state) ? array_filter(array_map('trim', explode(',', $state))) : ($state ?? []));
                            })
                            ->dehydrateStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state)
                            ->columns(4)
                            ->columnSpanFull(),
                        Textarea::make('affected_individuals')
                            ->label('Categorie di interessati coinvolti')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Cause e Azioni')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->columns(1)
                    ->schema([
                        Textarea::make('root_cause')
                            ->label('Causa principale')
                            ->rows(2),
                        Textarea::make('corrective_actions')
                            ->label('Azioni correttive immediate')
                            ->rows(3),
                        Textarea::make('preventive_measures')
                            ->label('Misure preventive adottate')
                            ->rows(3),
                        Textarea::make('mitigation_actions')
                            ->label('Azioni di mitigazione')
                            ->rows(3),
                    ]),

                Section::make('Obblighi di Notifica (Art. 33-34 GDPR)')
                    ->icon('heroicon-o-bell-alert')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_notifiable_to_authority')
                            ->label('Notifica al Garante (entro 72h)')
                            ->helperText('Obbligatorio se rischio per i diritti degli interessati')
                            ->default(false),
                        Toggle::make('is_notifiable_to_subjects')
                            ->label('Comunicazione agli Interessati')
                            ->helperText('Obbligatorio se rischio elevato')
                            ->default(false),
                    ]),
            ]);
    }
}
