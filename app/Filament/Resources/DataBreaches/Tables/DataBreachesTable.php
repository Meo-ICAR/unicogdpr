<?php

namespace App\Filament\Resources\DataBreaches\Tables;

use App\Models\DataBreach;
use App\Services\DocumentGeneratorService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class DataBreachesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Incidente')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->limit(50),
                BadgeColumn::make('severity')
                    ->label('Gravità')
                    ->sortable()
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'low',
                        'heroicon-o-exclamation-circle' => 'medium',
                        'heroicon-o-fire' => 'high',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => '🟢 Bassa',
                        'medium' => '🟡 Media',
                        'high' => '🔴 Alta',
                        default => ucfirst($state),
                    }),
                BadgeColumn::make('status')
                    ->label('Stato')
                    ->sortable()
                    ->colors([
                        'warning' => 'investigating',
                        'info' => 'contained',
                        'success' => 'resolved',
                        'primary' => 'notified',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'investigating' => 'In indagine',
                        'contained' => 'Contenuto',
                        'resolved' => 'Risolto',
                        'notified' => 'Notificato',
                        default => ucfirst($state),
                    }),
                IconColumn::make('is_notifiable_to_authority')
                    ->label('Notifica Garante')
                    ->boolean()
                    ->trueIcon('heroicon-o-bell-alert')
                    ->falseIcon('heroicon-o-bell-slash')
                    ->trueColor('danger')
                    ->falseColor('gray'),
                IconColumn::make('is_notifiable_to_subjects')
                    ->label('Notifica Interessati')
                    ->boolean()
                    ->trueIcon('heroicon-o-users')
                    ->falseIcon('heroicon-o-user-minus')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                TextColumn::make('discovered_at')
                    ->label('Scoperto il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('authority_deadline')
                    ->label('Garante 72h')
                    ->state(fn (DataBreach $record) => match ($record->authorityNotificationState()) {
                        'not_required' => 'Non dovuta',
                        'done' => 'Notificato '.$record->authority_notified_at?->format('d/m H:i'),
                        'overdue' => 'SCADUTO ('.$record->authorityNotificationDeadline()?->format('d/m H:i').')',
                        'due_soon' => 'Entro '.$record->authorityNotificationDeadline()?->format('d/m H:i'),
                        default => 'Entro '.$record->authorityNotificationDeadline()?->format('d/m H:i'),
                    })
                    ->badge()
                    ->color(fn (DataBreach $record) => match ($record->authorityNotificationState()) {
                        'done' => 'success',
                        'overdue' => 'danger',
                        'due_soon' => 'warning',
                        'not_required' => 'gray',
                        default => 'info',
                    }),
                TextColumn::make('approximate_records_count')
                    ->label('N° record')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('discovered_at', 'desc')
            ->filters([
                SelectFilter::make('severity')
                    ->label('Gravità')
                    ->options([
                        'low' => '🟢 Bassa',
                        'medium' => '🟡 Media',
                        'high' => '🔴 Alta',
                    ]),
                SelectFilter::make('status')
                    ->label('Stato')
                    ->options([
                        'investigating' => 'In indagine',
                        'contained' => 'Contenuto',
                        'resolved' => 'Risolto',
                        'notified' => 'Notificato',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('mark_authority_notified')
                    ->label('Notificato al Garante')
                    ->icon('heroicon-o-bell-alert')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (DataBreach $record) => $record->is_notifiable_to_authority && ! $record->authority_notified_at)
                    ->action(function (DataBreach $record): void {
                        $record->update([
                            'authority_notified_at' => now(),
                            'status' => $record->status === 'investigating' ? 'notified' : $record->status,
                        ]);
                        activity('data_breach')->performedOn($record)->log('Notifica al Garante registrata');
                        Notification::make()->title('Notifica al Garante registrata')->success()->send();
                    }),
                Action::make('mark_subjects_notified')
                    ->label('Comunicato agli interessati')
                    ->icon('heroicon-o-users')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (DataBreach $record) => $record->is_notifiable_to_subjects && ! $record->subjects_notified_at)
                    ->action(function (DataBreach $record): void {
                        $record->update(['subjects_notified_at' => now()]);
                        activity('data_breach')->performedOn($record)->log('Comunicazione agli interessati registrata');
                        Notification::make()->title('Comunicazione agli interessati registrata')->success()->send();
                    }),
                Action::make('generate_dossier')
                    ->label('Dossier Notifica (PDF)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('danger')
                    ->action(function (DataBreach $record, DocumentGeneratorService $service) {
                        $pdf = $service->generateNotificaDataBreach($record);
                        $fileName = 'Dossier_DataBreach_'.Str::slug($record->name).'.pdf';

                        return response()->streamDownload(
                            fn () => print ($pdf->output()),
                            $fileName,
                            ['Content-Type' => 'application/pdf']
                        );
                    }),
                Action::make('generate_segnalazione')
                    ->label('Modulo Segnalazione (PDF)')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->action(function (DataBreach $record, DocumentGeneratorService $service) {
                        $pdf = $service->generateSegnalazioneDataBreach($record);
                        $fileName = 'Modulo_Segnalazione_DataBreach_'.Str::slug($record->name).'.pdf';

                        return response()->streamDownload(
                            fn () => print ($pdf->output()),
                            $fileName,
                            ['Content-Type' => 'application/pdf']
                        );
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
