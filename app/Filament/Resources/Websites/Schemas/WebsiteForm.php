<?php

namespace App\Filament\Resources\Websites\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WebsiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sito')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome sito')
                            ->required(),
                        TextInput::make('domain')
                            ->label('Dominio')
                            ->required()
                            ->url(fn ($record) => $record?->domain ? (str_starts_with($record->domain, 'http') ? $record->domain : "https://{$record->domain}") : null),
                        Select::make('type')
                            ->label('Tipologia')
                            ->options([
                                'istituzionale' => 'Istituzionale',
                                'social' => 'Social',
                                'landing' => 'Landing',
                                'vetrina' => 'Vetrina',
                                'e-commerce' => 'E-commerce',
                                'altro' => 'Altro',
                            ]),
                        Toggle::make('is_active')
                            ->label('Attivo')
                            ->default(true),
                        Toggle::make('is_typical')
                            ->label('Uso tipico aziendale'),
                        Toggle::make('is_iso27001_certified')
                            ->label('Infrastruttura certificata ISO 27001'),
                    ]),

                Section::make('Compliance')
                    ->columns(2)
                    ->schema([
                        TextInput::make('url_privacy')
                            ->label('URL privacy policy'),
                        DatePicker::make('privacy_date')
                            ->label('Ultimo aggiornamento privacy'),
                        TextInput::make('url_cookies')
                            ->label('URL cookie policy'),
                        TextInput::make('url_transparency')
                            ->label('URL trasparenza')
                            ->visible(fn ($get) => $get('type') === 'istituzionale'),
                        DatePicker::make('transparency_date')
                            ->label('Ultimo aggiornamento trasparenza')
                            ->visible(fn ($get) => $get('type') === 'istituzionale'),
                        Toggle::make('is_footercompilant')
                            ->label('Footer conforme GDPR'),
                    ]),
            ]);
    }
}
