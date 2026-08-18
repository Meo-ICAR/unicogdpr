<?php

namespace App\Filament\Resources\ExternalProcessorResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuditsRelationManager extends RelationManager
{
    protected static string $relationship = 'audits';

    protected static ?string $title = 'Storico Audit';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Titolo Audit')
                    ->required()
                    ->maxLength(255),

                DatePicker::make('audit_date')
                    ->label('Data Audit')
                    ->required()
                    ->default(now()),

                Select::make('status')
                    ->label('Stato Avanzamento')
                    ->options([
                        'planned' => 'Pianificato',
                        'pending_answers' => 'In attesa del fornitore',
                        'under_review' => 'In valutazione',
                        'completed' => 'Completato',
                    ])
                    ->required(),

                Select::make('result')
                    ->label('Esito Finale')
                    ->options([
                        'compliant' => 'Conforme',
                        'compliant_with_conditions' => 'Conforme con Prescrizioni',
                        'non_compliant' => 'NON Conforme',
                    ])
                    ->native(false),

                DatePicker::make('next_audit_due')
                    ->label('Prossima Scadenza (Next Audit)'),

                RichEditor::make('corrective_actions')
                    ->label('Azioni Correttive Richieste')
                    ->columnSpanFull(),

                Textarea::make('dpo_notes')
                    ->label('Note Interne (DPO)')
                    ->columnSpanFull(),

                // Componente Spatie per Google Drive
                SpatieMediaLibraryFileUpload::make('evidenze')
                    ->label('Evidenze e Questionari (Salvati su GDrive)')
                    ->collection('audit_evidences')
                    ->disk('google')
                    ->multiple()
                    ->downloadable()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')->label('Titolo'),
                TextColumn::make('audit_date')->date()->label('Data'),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->colors([
                        'warning' => 'pending_answers',
                        'info' => 'under_review',
                        'success' => 'completed',
                        'gray' => 'planned',
                    ]),
                TextColumn::make('result')
                    ->label('Esito')
                    ->badge()
                    ->colors([
                        'success' => 'compliant',
                        'warning' => 'compliant_with_conditions',
                        'danger' => 'non_compliant',
                    ]),
                TextColumn::make('next_audit_due')
                    ->date()
                    ->label('Scadenza Prossimo')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([

                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('audit_date', 'desc'); // Mostra i più recenti in alto
    }
}
