<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Filament\Concerns\HasNominaIncaricatoBulkActions;
use App\Models\Employee;
use App\Services\DocumentGeneratorService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class EmployeesTable
{
    use HasNominaIncaricatoBulkActions;

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
                Action::make('generate_nomina')
                    ->label('Nomina Art. 29 (PDF)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->action(function (Employee $record, DocumentGeneratorService $service) {
                        $pdf = $service->generateNominaIncaricato($record);
                        $fileName = 'Nomina_Art29_'.Str::slug($record->full_name).'.pdf';

                        return response()->streamDownload(
                            fn () => print ($pdf->output()),
                            $fileName,
                            ['Content-Type' => 'application/pdf']
                        );
                    }),
                Action::make('generate_nda')
                    ->label('Accordo NDA (PDF)')
                    ->icon('heroicon-o-shield-check')
                    ->color('gray')
                    ->action(function (Employee $record, DocumentGeneratorService $service) {
                        $pdf = $service->generateAccordoRiservatezza($record);
                        $fileName = 'Accordo_NDA_'.Str::slug($record->full_name).'.pdf';

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
                    static::generateNominaBulkAction(),
                    static::downloadNominaBulkAction(),
                    static::uploadSignedNominaBulkAction(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
