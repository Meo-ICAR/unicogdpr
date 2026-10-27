<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use App\Models\Company;
use App\Services\SuiteImport\ClientImporter;
use App\Services\SuiteImport\SuiteCompanyResolver;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->importFromSuiteAction(),
            CreateAction::make(),
        ];
    }

    private function importFromSuiteAction(): Action
    {
        return Action::make('importFromSuite')
            ->label('Importa dalla suite')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('gray')
            ->visible(fn (): bool => ClientResource::canCreate())
            ->modalHeading('Importa un cliente dalla suite')
            ->modalDescription('Indica l\'id suite, il codice fiscale o la partita IVA del cliente.')
            ->schema([
                TextInput::make('key')
                    ->label('Id suite, codice fiscale o partita IVA')
                    ->required()
                    ->maxLength(50),
            ])
            ->action(function (array $data): void {
                if (! ClientResource::canCreate()) {
                    abort(403);
                }

                $tenant = Filament::getTenant();

                try {
                    if (! $tenant instanceof Company || blank($tenant->vat_number)) {
                        throw new RuntimeException('La società corrente non ha una partita IVA: impossibile risalire alla società nella suite.');
                    }

                    $companies = app(SuiteCompanyResolver::class)->resolve((string) $tenant->vat_number);
                    app(ClientImporter::class)->importOne((string) $data['key'], $tenant, $companies['suite']);
                } catch (QueryException) {
                    // Mai il messaggio SQL: i binding contengono dati personali.
                    Log::error('Import cliente suite: errore del database.', ['company_id' => $tenant?->getKey()]);

                    Notification::make()
                        ->title('Importazione non riuscita')
                        ->body("Errore del database durante l'importazione: nessun dato salvato.")
                        ->danger()
                        ->send();

                    return;
                } catch (ModelNotFoundException|InvalidArgumentException|RuntimeException $exception) {
                    Notification::make()
                        ->title('Importazione non riuscita')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Cliente importato')
                    ->success()
                    ->send();
            });
    }
}
