<?php

namespace App\Filament\Resources\AuditChecklistEvaluations\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Trattamenti aziendali (ProcessingActivity, registro Art. 30) coinvolti da
 * questa voce di checklist valutata, ciascuno con il paragrafo/sezione del
 * documento citato pertinente (campi pivot su checklist_eval_processing_activities).
 */
class ProcessingActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'processingActivities';

    protected static ?string $title = 'Trattamenti Aziendali Coinvolti';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('paragraph')
                ->label('Paragrafo/sezione pertinente')
                ->maxLength(255),
            TextInput::make('note')
                ->label('Nota'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->label('Trattamento'),
                TextColumn::make('pivot.paragraph')->label('Paragrafo'),
                TextColumn::make('pivot.note')->label('Nota'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        TextInput::make('paragraph')
                            ->label('Paragrafo/sezione pertinente')
                            ->maxLength(255),
                        TextInput::make('note')
                            ->label('Nota'),
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make(),
            ]);
    }
}
