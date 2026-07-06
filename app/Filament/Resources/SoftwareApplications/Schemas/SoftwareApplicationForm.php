<?php

namespace App\Filament\Resources\SoftwareApplications\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\BelongsToSelect;

class SoftwareApplicationForm
{
    public static function configure(Schema ): Schema
    {
        return 
            ->components([
                TextInput::make('company_id')->label('Company Id')->maxLength(255),
                TextInput::make('software_category_id')->label('Software Category Id')->maxLength(255),
                TextInput::make('name')->label('Name')->maxLength(255),
                TextInput::make('provider_name')->label('Provider Name')->maxLength(255),
                TextInput::make('website_url')->label('Website Url')->maxLength(255),
                TextInput::make('api_url')->label('Api Url')->maxLength(255),
                TextInput::make('sandbox_url')->label('Sandbox Url')->maxLength(255),
                TextInput::make('api_key_url')->label('Api Key Url')->maxLength(255),
                TextInput::make('api_parameters')->label('Api Parameters')->maxLength(255),
                TextInput::make('is_cloud')->label('Is Cloud')->maxLength(255),
                TextInput::make('is_data_eu')->label('Is Data Eu')->maxLength(255),
                TextInput::make('is_iso27001_certified')->label('Is Iso27001 Certified')->maxLength(255),
                TextInput::make('apikey')->label('Apikey')->maxLength(255),
                TextInput::make('wallet_balance')->label('Wallet Balance')->maxLength(255),
            ]);
    }
}
