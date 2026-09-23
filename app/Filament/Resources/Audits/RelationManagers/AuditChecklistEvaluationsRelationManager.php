<?php

namespace App\Filament\Resources\Audits\RelationManagers;

use App\Enums\AuditChecklistGapStatus;
use App\Filament\Resources\AuditChecklistEvaluations\AuditChecklistEvaluationResource;
use App\Filament\Resources\AuditChecklistEvaluations\Schemas\AuditChecklistEvaluationForm;
use App\Models\Audit;
use App\Models\AuditChecklistEvaluation;
use App\Models\AuditChecklistItem;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditChecklistEvaluationsRelationManager extends RelationManager
{
    protected static string $relationship = 'checklistEvaluations';

    protected static ?string $title = 'Checklist Documentale (Gap Analysis)';

    public function form(Schema $schema): Schema
    {
        return AuditChecklistEvaluationForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('gap_status')
            ->columns([
                TextColumn::make('checklistItem.category')
                    ->label('Categoria')
                    ->badge(),
                TextColumn::make('checklistItem.title')
                    ->label('Voce di checklist')
                    ->wrap(),
                TextColumn::make('externalProcessor.name')
                    ->label('Vendor'),
                TextColumn::make('gap_status')
                    ->label('Esito')
                    ->badge(),
                TextColumn::make('next_review_at')
                    ->label('Prossima riverifica')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('gap_status')
                    ->label('Esito')
                    ->options(AuditChecklistGapStatus::options()),
            ])
            ->headerActions([
                CreateAction::make(),
                /**
                 * Popola in un colpo solo una riga di valutazione (stato "Da
                 * Verificare") per ogni voce attiva del catalogo non ancora
                 * valutata per questo audit, cosi' l'auditor scorre la
                 * checklist fissa invece di aggiungere le voci una a una.
                 */
                Action::make('generate_missing')
                    ->label('Genera valutazioni mancanti')
                    ->icon('heroicon-o-sparkles')
                    ->color('gray')
                    ->action(function () {
                        /** @var Audit $audit */
                        $audit = $this->getOwnerRecord();

                        $alreadyEvaluated = $audit->checklistEvaluations()->pluck('audit_checklist_item_id');

                        $missingItems = AuditChecklistItem::where('is_active', true)
                            ->whereNotIn('id', $alreadyEvaluated)
                            ->get();

                        foreach ($missingItems as $item) {
                            AuditChecklistEvaluation::create([
                                'audit_id' => $audit->id,
                                'audit_checklist_item_id' => $item->id,
                                'gap_status' => AuditChecklistGapStatus::DaVerificare->value,
                            ]);
                        }

                        Notification::make()
                            ->title($missingItems->count().' voci di checklist aggiunte da valutare')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Apri scheda')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (AuditChecklistEvaluation $record) => AuditChecklistEvaluationResource::getUrl('edit', ['record' => $record])),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
