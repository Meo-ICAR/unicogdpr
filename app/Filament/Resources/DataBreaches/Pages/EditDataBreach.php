<?php

namespace App\Filament\Resources\DataBreaches\Pages;

use App\Filament\Resources\DataBreaches\DataBreachResource;
use App\Models\DataBreach;
use App\Services\DocumentGeneratorService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditDataBreach extends EditRecord
{
    protected static string $resource = DataBreachResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_segnalazione')
                ->label('Stampa Modulo Segnalazione (PDF)')
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
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
