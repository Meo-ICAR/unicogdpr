<?php

namespace App\Filament\Resources\Audits\Tables;

use App\Enums\AuditStatus;
use App\Models\Audit;
use App\Models\Company;
use App\Services\Drive\GoogleDriveZipExporter;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('executed_at', 'desc')
            ->columns([
                TextColumn::make('company.name')
                    ->label('Azienda')
                    ->badge()
                    ->color('info'),
                TextColumn::make('auditable.name')
                    ->label('Mandante / Soggetto')
                    ->badge()
                    ->color('success')
                    ->placeholder('—'),
                TextColumn::make('auditor_name')
                    ->label('Auditor')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('auditable_type')
                    ->label('Tipo Soggetto')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('protocol_number')
                    ->label('Protocollo')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge(),
                TextColumn::make('executed_at')
                    ->label('Eseguito il')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('followup_date')
                    ->label('Follow-up')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $record?->followup_date?->isPast() ? 'danger' : null),
            ])
            ->filters([
                // audits vive su mysql_unicooam, companies sulla connessione
                // di default: niente ->relationship() (genererebbe una
                // whereHas cross-connection), filtro diretto sulla colonna
                // scalare company_id con opzioni lette a parte.
                SelectFilter::make('company_id')
                    ->label('Azienda')
                    ->options(fn (): array => Company::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    // Resource non tenant-scoped (vedi commento su
                    // $isScopedToTenant in AuditResource): preimpostiamo qui
                    // il filtro sulla company attualmente selezionata nel
                    // pannello, così la vista di default resta comunque
                    // limitata al tenant corrente.
                    ->default(fn (): ?string => Filament::getTenant()?->id),
                SelectFilter::make('status')
                    ->label('Stato')
                    ->options(AuditStatus::options()),
            ])
            ->recordActions([
                Action::make('download_drive_zip')
                    ->label('Scarica Documenti (ZIP)')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('gray')
                    ->visible(fn (Audit $record) => filled($record->drive_folder_id))
                    ->action(function (Audit $record) {
                        try {
                            $zipPath = (new GoogleDriveZipExporter)->exportFolderToZip(
                                $record->drive_folder_id,
                                'audit-'.$record->id.'.zip',
                            );
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Errore durante il download da Google Drive')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();

                            return;
                        }

                        $fileName = 'Audit-'.($record->protocol_number ?: $record->id).'-'.now()->format('Y-m-d').'.zip';

                        return response()->download($zipPath, $fileName)
                            ->deleteFileAfterSend(true);
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
