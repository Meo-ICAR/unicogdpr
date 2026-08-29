<?php

namespace App\Filament\Resources\Employees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('last_name')
                    ->label('Cognome')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('department')
                    ->label('Reparto')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('job_title')
                    ->label('Mansione')
                    ->searchable(),
                TextColumn::make('hired_at')
                    ->label('Assunto il')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('generate_nomina')
                    ->label('Nomina Art. 29 (PDF)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->action(function (\App\Models\Employee $record, \App\Services\DocumentGeneratorService $service) {
                        $pdf = $service->generateNominaIncaricato($record);
                        $fileName = 'Nomina_Art29_' . \Illuminate\Support\Str::slug($record->full_name) . '.pdf';
                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            $fileName,
                            ['Content-Type' => 'application/pdf']
                        );
                    }),
                \Filament\Actions\Action::make('generate_nda')
                    ->label('Accordo NDA (PDF)')
                    ->icon('heroicon-o-shield-check')
                    ->color('gray')
                    ->action(function (\App\Models\Employee $record, \App\Services\DocumentGeneratorService $service) {
                        $pdf = $service->generateAccordoRiservatezza($record);
                        $fileName = 'Accordo_NDA_' . \Illuminate\Support\Str::slug($record->full_name) . '.pdf';
                        return response()->streamDownload(
                            fn () => print($pdf->output()),
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
