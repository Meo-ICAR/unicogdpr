<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use App\Models\Company;
use App\Models\Document;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

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
            DeleteAction::make(),
        ];
    }
}
