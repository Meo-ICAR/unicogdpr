<?php

namespace App\Filament\Resources\RegistroTrattamentiItems\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RegistroTrattamentiItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Attività di Trattamento (Art. 30 GDPR)')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->columns(2)
                    ->schema([
                        TextInput::make('activity')
                            ->label('Nome / Attività di Trattamento')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Es. Gestione del Personale, Fatturazione, Marketing'),
                        TextInput::make('purpose')
                            ->label('Finalità del Trattamento')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Es. Adempimenti contabili e fiscali di legge'),
                        TextInput::make('legal_basis')
                            ->label('Base Giuridica (Art. 6 / 9)')
                            ->placeholder('Es. Obbligo di legge, Consenso, Legittimo interesse'),
                        TextInput::make('retention_period')
                            ->label('Tempi di Conservazione')
                            ->placeholder('Es. 10 anni ex Art. 2220 c.c., 24 mesi'),
                    ]),

                Section::make('Interessati e Categorie di Dati')
                    ->icon('heroicon-o-user-group')
                    ->columns(2)
                    ->schema([
                        TextInput::make('data_subjects')
                            ->label('Categorie di Interessati')
                            ->placeholder('Es. Dipendenti, Clienti, Fornitori')
                            ->columnSpanFull(),
                        Textarea::make('data_categories')
                            ->label('Categorie di Dati Personali Trattati')
                            ->rows(3)
                            ->placeholder('Dati anagrafici, fiscali, bancari, di contatto...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Destinatari e Misure di Sicurezza')
                    ->icon('heroicon-o-shield-check')
                    ->columns(2)
                    ->schema([
                        Textarea::make('recipients')
                            ->label('Destinatari / Responsabili Esterni')
                            ->rows(2)
                            ->placeholder('Consulente del lavoro, Banche, Fornitori Cloud...')
                            ->columnSpanFull(),
                        Toggle::make('is_extra_eu_transfer')
                            ->label('Trasferimento dati Extra-UE')
                            ->helperText('Indica se i dati vengono trasferiti verso Paesi terzi non appartenenti allo SEE')
                            ->default(false),
                        Textarea::make('security_measures')
                            ->label('Misure di Sicurezza Adottate (Art. 32)')
                            ->rows(3)
                            ->placeholder('Cifratura, Backup giornalieri, Autenticazione a due fattori, Controlli di accesso...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
