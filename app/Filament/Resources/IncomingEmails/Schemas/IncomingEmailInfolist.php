<?php

namespace App\Filament\Resources\IncomingEmails\Schemas;

use App\Models\IncomingEmail;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class IncomingEmailInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Messaggio')
                ->columns(2)
                ->schema([
                    TextEntry::make('from_name')
                        ->label('Mittente')
                        ->formatStateUsing(fn ($state, IncomingEmail $record) => trim(($state ?? '').' <'.$record->from_email.'>')),
                    TextEntry::make('received_at')
                        ->label('Ricevuta il')
                        ->dateTime('d/m/Y H:i'),
                    TextEntry::make('subject')
                        ->label('Oggetto')
                        ->columnSpanFull(),
                    TextEntry::make('classification')
                        ->label('Classificazione')
                        ->badge(),
                    TextEntry::make('mailAccount.name')
                        ->label('Casella')
                        ->placeholder('—'),
                    TextEntry::make('dataSubjectRequest.id')
                        ->label('DSAR collegata')
                        ->formatStateUsing(fn ($state) => $state ? "DSAR #{$state}" : null)
                        ->placeholder('Nessuna')
                        ->columnSpanFull(),
                ]),

            Section::make('Contenuto')
                ->schema([
                    TextEntry::make('body_text')
                        ->label('')
                        ->placeholder('(nessun corpo testuale)')
                        ->formatStateUsing(fn ($state, IncomingEmail $record) => $state
                            ?: Str::of((string) $record->body_html)->stripTags()->squish()->limit(5000))
                        ->prose(),
                ]),

            Section::make('Conversazione')
                ->visible(fn (IncomingEmail $record) => $record->threadMessages()->exists())
                ->schema([
                    TextEntry::make('thread')
                        ->label('')
                        ->state(fn (IncomingEmail $record) => $record->threadMessages()->get()
                            ->map(fn (IncomingEmail $m) => $m->received_at?->format('d/m/Y H:i').' — '.$m->from_email.' — '.$m->subject)
                            ->all())
                        ->listWithLineBreaks()
                        ->bulleted(),
                ]),

            Section::make('Allegati')
                ->visible(fn (IncomingEmail $record) => $record->getMedia('email_attachments')->isNotEmpty())
                ->schema([
                    TextEntry::make('attachments')
                        ->label('')
                        ->state(fn (IncomingEmail $record) => $record->getMedia('email_attachments')
                            ->map(fn ($media) => $media->file_name.' ('.round($media->size / 1024).' KB)')
                            ->all())
                        ->listWithLineBreaks()
                        ->bulleted(),
                ]),
        ]);
    }
}
