<?php

namespace App\Filament\Resources\Dpias\Pages;

use App\Filament\Resources\Dpias\DpiaResource;
use App\Models\Dpia;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class EditDpia extends EditRecord
{
    protected static string $resource = DpiaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sign_off')
                ->label('Firma e Completa DPIA (Parere DPO)')
                ->icon('heroicon-o-shield-check')
                ->color('success')
                ->visible(fn (Dpia $record) => ! $record->isSignedByDpo())
                ->requiresConfirmation()
                ->modalDescription('La validazione formale del DPO chiude la DPIA e ne blocca il contenuto con un\'impronta SHA-256. L\'operazione non è reversibile da qui.')
                ->action(function (Dpia $record) {
                    try {
                        $record->signOffByDpo(Auth::user());

                        Notification::make()
                            ->title('DPIA validata e completata')
                            ->success()
                            ->send();

                        $this->fillForm();
                    } catch (RuntimeException $e) {
                        Notification::make()
                            ->title('Impossibile completare la validazione')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
