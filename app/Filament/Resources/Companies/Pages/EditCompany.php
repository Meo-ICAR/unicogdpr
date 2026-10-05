<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use App\Models\Company;
use App\Models\Document;
use App\Services\Drive\DocumentDriveSync;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
use Throwable;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload_logo')
                ->label('Carica Logo')
                ->icon('heroicon-o-photo')
                ->color('gray')
                ->schema([
                    FileUpload::make('logo')
                        ->label('Logo aziendale')
                        ->disk('local')
                        ->image()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    /** @var Company $company */
                    $company = $this->getRecord();

                    $document = $company->documents()
                        ->where('docnumber', Company::LOGO_DOCNUMBER)
                        ->first() ?? Document::create([
                            'company_id' => $company->id,
                            'documentable_type' => 'company',
                            'documentable_id' => $company->id,
                            'docnumber' => Company::LOGO_DOCNUMBER,
                            'name' => 'Logo aziendale',
                            'status' => 'approved',
                        ]);

                    $document->clearMediaCollection('documents');
                    $document->addMedia(Storage::disk('local')->path($data['logo']))
                        ->toMediaCollection('documents');

                    Notification::make()
                        ->title('Logo caricato')
                        ->success()
                        ->send();
                }),
            Action::make('sync_drive')
                ->label('Sincronizza su Drive')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->action(function (): void {
                    /** @var Company $company */
                    $company = $this->getRecord();

                    try {
                        $result = app(DocumentDriveSync::class)->syncCompanyDocuments($company);
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('Sincronizzazione non riuscita')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Sincronizzazione Drive completata')
                        ->body("Caricati: {$result['synced']} · Falliti: {$result['failed']} · Già sincronizzati/ignorati: {$result['skipped']}")
                        ->status($result['failed'] > 0 ? 'warning' : 'success')
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
