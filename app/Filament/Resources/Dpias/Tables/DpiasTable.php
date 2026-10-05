<?php

namespace App\Filament\Resources\Dpias\Tables;

use App\Models\Dpia;
use App\Services\DocumentGeneratorService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class DpiasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nome')->sortable()->searchable(),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'completed' => 'success',
                        'under_review' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                IconColumn::make('dpo_signed_at')
                    ->label('Validata DPO')
                    ->boolean()
                    ->getStateUsing(fn (Dpia $record) => $record->isSignedByDpo()),
                TextColumn::make('completion_date')->label('Data completamento')->date()->sortable(),
                TextColumn::make('next_review_date')->label('Prossima revisione')->date()->sortable(),
                TextColumn::make('created_at')->label('Creato il')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('download_report')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->action(function (Dpia $record, DocumentGeneratorService $service) {
                        $pdf = $service->generateDpiaReport($record);
                        $fileName = 'DPIA_'.Str::slug($record->name).'.pdf';

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
