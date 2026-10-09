<?php

namespace App\Filament\Resources\IncomingEmails\Schemas;

use App\Filament\Resources\IncomingEmails\IncomingEmailResource;
use App\Models\IncomingEmail;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
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
                        ->placeholder('Nessuna'),
                    TextEntry::make('complaintRegistry.protocol_number')
                        ->label('Reclamo collegato')
                        ->formatStateUsing(fn ($state, IncomingEmail $record) => $state ? "{$state} (evento #{$record->complaintRegistry?->event_sequence})" : null)
                        ->placeholder('Nessuno')
                        ->columnSpanFull(),
                ]),

            Section::make('Contenuto')
                ->schema([
                    TextEntry::make('body_html')
                        ->label('')
                        ->visible(fn (IncomingEmail $record) => $record->displayHtml() !== null)
                        ->state(fn (IncomingEmail $record) => $record->displayHtml())
                        ->html()
                        ->prose()
                        ->columnSpanFull(),
                    TextEntry::make('body_text')
                        ->label('')
                        ->visible(fn (IncomingEmail $record) => $record->displayHtml() === null)
                        ->placeholder('(nessun corpo)')
                        ->prose(),
                ]),

            Section::make('Conversazione')
                ->visible(fn (IncomingEmail $record) => $record->threadMessages()->exists())
                ->schema([
                    RepeatableEntry::make('thread')
                        ->label('')
                        ->state(fn (IncomingEmail $record) => $record->threadMessages()->get()
                            ->map(fn (IncomingEmail $m) => [
                                'when' => $m->received_at?->format('d/m/Y H:i'),
                                'from' => trim($m->from_name.' <'.$m->from_email.'>'),
                                'subject' => $m->subject,
                                'excerpt' => Str::of($m->body_text ?: strip_tags((string) $m->body_html))->squish()->limit(300)->toString(),
                                'url' => IncomingEmailResource::getUrl('view', ['record' => $m]),
                                'attachments' => $m->getMedia('email_attachments')->count(),
                            ])
                            ->all())
                        ->columns(2)
                        ->schema([
                            TextEntry::make('when')->label('Ricevuta il'),
                            TextEntry::make('from')->label('Mittente'),
                            TextEntry::make('subject')
                                ->label('Oggetto')
                                ->columnSpanFull(),
                            TextEntry::make('excerpt')->label('Anteprima')->columnSpanFull(),
                            TextEntry::make('url')
                                ->label('')
                                ->formatStateUsing(fn () => 'Apri questa email')
                                ->url(fn (?string $state): ?string => $state),
                            TextEntry::make('attachments')
                                ->label('Allegati')
                                ->badge(),
                        ]),
                ]),

            Section::make('Allegati')
                ->visible(fn (IncomingEmail $record) => $record->getMedia('email_attachments')->isNotEmpty())
                ->schema([
                    RepeatableEntry::make('attachment_files')
                        ->label('')
                        ->state(fn (IncomingEmail $record) => $record->getMedia('email_attachments')
                            ->map(function ($media): array {
                                $available = Storage::disk($media->disk)->exists($media->getPathRelativeToRoot());

                                return [
                                    'name' => $media->file_name.' ('.round($media->size / 1024).' KB)'.($available ? '' : ' — file non disponibile'),
                                    'view_url' => $available ? route('incoming-email.attachment', $media) : null,
                                    'download_url' => $available ? route('incoming-email.attachment', [$media, 'download' => 1]) : null,
                                ];
                            })
                            ->all())
                        ->columns(3)
                        ->schema([
                            TextEntry::make('name')->label('File'),
                            TextEntry::make('view_url')
                                ->label('')
                                ->formatStateUsing(fn () => 'Apri')
                                ->visible(fn (?string $state): bool => filled($state))
                                ->icon('heroicon-o-eye')
                                ->url(fn (?string $state): ?string => $state)
                                ->openUrlInNewTab(),
                            TextEntry::make('download_url')
                                ->label('')
                                ->formatStateUsing(fn () => 'Scarica')
                                ->visible(fn (?string $state): bool => filled($state))
                                ->icon('heroicon-o-arrow-down-tray')
                                ->url(fn (?string $state): ?string => $state),
                        ]),
                ]),
        ]);
    }
}
