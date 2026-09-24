<?php

namespace App\Filament\Resources\IncomingEmails\Tables;

use App\Enums\EmailClassification;
use App\Filament\Resources\IncomingEmails\IncomingEmailResource;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IncomingEmailsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('received_at', 'desc')
            ->columns([
                IconColumn::make('is_read')
                    ->label('')
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope-open')
                    ->falseIcon('heroicon-s-envelope')
                    ->falseColor('warning'),
                TextColumn::make('from_name')
                    ->label('Mittente')
                    ->description(fn ($record) => $record->from_email)
                    ->searchable(['from_name', 'from_email'])
                    ->sortable(),
                TextColumn::make('subject')
                    ->label('Oggetto')
                    ->limit(60)
                    ->searchable()
                    ->weight(fn ($record) => $record->is_read ? null : 'semibold'),
                TextColumn::make('classification')
                    ->label('Classe')
                    ->badge(),
                TextColumn::make('mailAccount.name')
                    ->label('Casella')
                    ->toggleable(),
                IconColumn::make('data_subject_request_id')
                    ->label('DSAR')
                    ->boolean()
                    ->trueIcon('heroicon-o-link')
                    ->falseIcon('heroicon-o-minus')
                    ->falseColor('gray'),
                IconColumn::make('complaint_registry_id')
                    ->label('Reclamo')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-minus')
                    ->falseColor('gray'),
                TextColumn::make('received_at')
                    ->label('Ricevuta')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('mail_account_id')
                    ->label('Casella')
                    ->relationship('mailAccount', 'name'),
                SelectFilter::make('classification')
                    ->label('Classe')
                    ->options(EmailClassification::options()),
                Filter::make('unread')
                    ->label('Solo non lette')
                    ->toggle()
                    ->query(fn (Builder $query) => $query->where('is_read', false)),
                Filter::make('with_dsar')
                    ->label('Con DSAR collegata')
                    ->toggle()
                    ->query(fn (Builder $query) => $query->whereNotNull('data_subject_request_id')),
                Filter::make('with_complaint')
                    ->label('Con Reclamo collegato')
                    ->toggle()
                    ->query(fn (Builder $query) => $query->whereNotNull('complaint_registry_id')),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                ActionGroup::make(IncomingEmailResource::rowActions()),
            ]);
    }
}
