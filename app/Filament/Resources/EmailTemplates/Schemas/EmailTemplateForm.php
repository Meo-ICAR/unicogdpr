<?php

namespace App\Filament\Resources\EmailTemplates\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EmailTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dettagli Template')
                    ->icon('heroicon-o-envelope')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome del Template')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Es. Risposta Accesso Dati Art. 15'),
                        TextInput::make('code')
                            ->label('Codice Univoco')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(100)
                            ->placeholder('dsar_response_access'),
                        TextInput::make('subject')
                            ->label('Oggetto Email')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->placeholder('Es. Riscontro alla richiesta di accesso ex Art. 15 GDPR - {company_name}'),
                        Toggle::make('is_active')
                            ->label('Template Attivo')
                            ->default(true),
                        TagsInput::make('placeholders')
                            ->label('Segnaposto Disponibili')
                            ->placeholder('+ Aggiungi tag (es. {requester_name})')
                            ->helperText('Segnaposto supportati: {requester_name}, {deadline_at}, {request_type}, {company_name}, {received_at}')
                            ->columnSpanFull(),
                    ]),

                Section::make('Corpo del Messaggio')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        RichEditor::make('body_html')
                            ->label('Corpo Email (HTML)')
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('body_text')
                            ->label('Corpo Email (Testo Semplice - Fallback)')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
