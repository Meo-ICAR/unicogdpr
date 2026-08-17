<?php

namespace App\Filament\Resources\PrivacyRetentions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PrivacyRetentionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Regola di Conservazione')
                    ->icon('heroicon-o-clock')
                    ->columns(2)
                    ->schema([
                        TextInput::make('data_category')
                            ->label('Categoria di Dati')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Es. Dati contabili, Dati del personale'),
                        TextInput::make('purpose')
                            ->label('Finalità del Trattamento')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Es. Adempimento fiscale, Gestione contratti'),
                        TextInput::make('retention_value')
                            ->label('Durata')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        Select::make('retention_unit')
                            ->label('Unità di Misura')
                            ->options([
                                'hours'     => 'Ore',
                                'days'      => 'Giorni',
                                'months'    => 'Mesi',
                                'years'     => 'Anni',
                                'permanent' => 'Permanente',
                            ])
                            ->required(),
                        TextInput::make('start_trigger')
                            ->label('Evento di Avvio del Conteggio')
                            ->maxLength(255)
                            ->placeholder('Es. Chiusura contratto, Fine rapporto di lavoro'),
                        Select::make('legal_basis')
                            ->label('Base Giuridica dell\'Obbligo')
                            ->options(fn () => \App\Models\PrivacyLegalBase::orderBy('name')
                                ->pluck('name', 'name')
                                ->toArray())
                            ->searchable()
                            ->placeholder('Seleziona base giuridica'),
                        TextInput::make('legal_reference')
                            ->label('Normativa di Riferimento')
                            ->maxLength(255)
                            ->placeholder('Es. Art. 2220 c.c., D.Lgs. 196/2003'),
                        Select::make('end_action')
                            ->label('Azione alla Scadenza')
                            ->options([
                                'delete'        => 'Eliminazione definitiva',
                                'anonymize'     => 'Anonimizzazione',
                                'manual_review' => 'Revisione manuale',
                                'archive'       => 'Archiviazione',
                            ])
                            ->required(),
                    ]),
            ]);
    }
}
