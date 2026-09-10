<?php

namespace App\Filament\Resources\LeadReturnLogs\Schemas;

use App\Models\Client;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeadReturnLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Reso / KO')
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->label('Motivazione del reso')
                        ->options([
                            'bounce' => 'Recapito fallito (bounce)',
                            'opt_out_requested' => 'Richiesta di opt-out',
                            'converted' => 'Convertito',
                        ])
                        ->default('bounce')
                        ->required(),
                    DateTimePicker::make('reported_at')
                        ->label('Segnalato il')
                        ->default(now())
                        ->seconds(false)
                        ->required(),
                    Select::make('clientable_type')
                        ->label('Tipo soggetto collegato')
                        ->options([Client::class => 'Cliente'])
                        ->live()
                        ->afterStateUpdated(fn ($set) => $set('clientable_id', null))
                        ->helperText('Facoltativo: collega il reso a un nominativo in anagrafica.'),
                    Select::make('clientable_id')
                        ->label('Nominativo')
                        ->options(fn () => Client::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->visible(fn ($get) => filled($get('clientable_type'))),
                ]),
        ]);
    }
}
