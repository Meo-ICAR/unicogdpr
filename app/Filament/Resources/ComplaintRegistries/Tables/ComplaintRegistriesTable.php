<?php

namespace App\Filament\Resources\ComplaintRegistries\Tables;

use App\Enums\ComplaintStatus;
use App\Filament\Exports\DynamicGroupExport;
use App\Models\ComplaintRegistry;
use App\Services\DocumentGeneratorService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use pxlrbt\FilamentExcel\Actions\ExportAction;

class ComplaintRegistriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->orderBy('protocol_number')->orderBy('event_sequence'))
            ->columns([
                TextColumn::make('protocol_number')
                    ->label('ID Protocollo')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('event_sequence')
                    ->label('N° Prog.')
                    ->sortable(),
                TextColumn::make('event_at')
                    ->label('Data/Ora Evento')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                TextColumn::make('event_phase')
                    ->label('Fase / Tipo Evento')
                    ->wrap(),
                TextColumn::make('mandating_company')
                    ->label('Società Mandataria (Titolare)')
                    ->wrap(),
                TextColumn::make('master_agency')
                    ->label('Agenzia Master (Responsabile)')
                    ->wrap(),
                TextColumn::make('sub_supplier')
                    ->label('Sub-Fornitore / Call Center')
                    ->wrap(),
                TextColumn::make('caller_number')
                    ->label('Numerazione / Caller ID'),
                TextColumn::make('agcom_roc_compliance')
                    ->label('Conformità AGCOM / ROC')
                    ->wrap(),
                TextColumn::make('complainant_name')
                    ->label('Interessato / Reclamante')
                    ->searchable(),
                TextColumn::make('recapito')
                    ->label('Recapito (Tel / PEC)')
                    ->state(fn (ComplaintRegistry $record): string => trim(
                        $record->complainant_email.($record->complainant_phone ? ' / '.$record->complainant_phone : '')
                    )),
                TextColumn::make('description')
                    ->label('Oggetto & Dettaglio Evento')
                    ->wrap(),
                TextColumn::make('operational_action')
                    ->label('Azione Tecnico-Operativa')
                    ->wrap(),
                TextColumn::make('dnc_blacklist_status')
                    ->label('Blacklist DNC / SHA-256')
                    ->wrap(),
                TextColumn::make('sla_deadline_note')
                    ->label('Scadenza SLA / Termine Legal')
                    ->wrap(),
                TextColumn::make('log_freeze_retention')
                    ->label('Retention & Log Freeze')
                    ->wrap(),
                TextColumn::make('evidence_attachment')
                    ->label('Allegato / Evidence'),
                TextColumn::make('phase_status')
                    ->label('Stato Pratica')
                    ->badge(),
                TextColumn::make('assigned_to')
                    ->label('Incaricato / DPO'),
                TextColumn::make('status')
                    ->label('Stato (interno)')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deadline_at')
                    ->label('Scadenza (data)')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (ComplaintRegistry $record) => $record->isOverdue() ? 'danger' : null)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Stato')
                    ->options(ComplaintStatus::options()),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        DynamicGroupExport::make('registro_reclami'),
                    ])
                    ->label('Esporta Excel')
                    ->color('success'),
            ])
            ->recordActions([
                Action::make('generate_scheda')
                    ->label('Scheda (PDF)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('danger')
                    ->action(function (ComplaintRegistry $record, DocumentGeneratorService $service) {
                        $pdf = $service->generateSchedaReclamo($record->protocol_number);
                        $fileName = 'Scheda_Reclamo_'.Str::slug($record->protocol_number).'.pdf';

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
                ]),
            ]);
    }
}
