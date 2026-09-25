<?php

namespace App\Filament\Resources\AuditChecklistEvaluations\Schemas;

use App\Enums\AuditChecklistGapStatus;
use App\Filament\Resources\AuditChecklistEvaluations\AuditChecklistEvaluationResource;
use App\Models\AuditChecklistEvaluation;
use App\Models\AuditChecklistItem;
use App\Models\ExternalProcessor;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AuditChecklistEvaluationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->disabled(AuditChecklistEvaluationResource::isCompanyAdminPanel())
            ->components([
                Section::make('Audit di Riferimento')
                    ->icon('heroicon-o-magnifying-glass-circle')
                    ->columns(2)
                    ->schema([
                        Placeholder::make('mandante')
                            ->label('Mandante')
                            ->content(fn (?AuditChecklistEvaluation $record) => $record?->audit?->auditable?->name ?? '—'),
                        Placeholder::make('data_audit')
                            ->label('Data Audit')
                            ->content(fn (?AuditChecklistEvaluation $record) => $record?->audit?->executed_at?->format('d/m/Y') ?? '—'),
                    ])
                    ->visible(fn (?AuditChecklistEvaluation $record) => $record !== null),

                Section::make('Voce di Checklist')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->columns(2)
                    ->schema([
                        Select::make('audit_checklist_item_id')
                            ->label('Voce di checklist')
                            ->options(fn () => AuditChecklistItem::orderBy('category')->orderBy('sort_order')->get()
                                ->mapWithKeys(fn (AuditChecklistItem $item) => [$item->id => "[{$item->category->label()}] {$item->title}"]))
                            ->searchable()
                            ->required()
                            ->columnSpanFull(),
                        Select::make('external_processor_id')
                            ->label('Sub-fornitore / Vendor coinvolto')
                            ->options(fn () => ExternalProcessor::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),
                        Toggle::make('is_vendor_scope')
                            ->label('Gap attribuibile al vendor')
                            ->default(false),
                    ]),

                Section::make('Documentazione ed Evidenze')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->schema([
                        Textarea::make('internal_documentation')
                            ->label('Documentazione interna / policy disponibile')
                            ->rows(3),
                        Textarea::make('vendor_evidence_notes')
                            ->label('Evidenza caso specifico / sub-fornitore')
                            ->rows(3),
                    ]),

                Section::make('Valutazione & Gap Analysis')
                    ->icon('heroicon-o-check-badge')
                    ->columns(2)
                    ->schema([
                        Select::make('gap_status')
                            ->label('Esito')
                            ->options(AuditChecklistGapStatus::options())
                            ->default(AuditChecklistGapStatus::DaVerificare->value)
                            ->required(),
                        TextInput::make('verified_by')
                            ->label('Verificato da'),
                        DatePicker::make('verified_at')
                            ->label('Verificato il'),
                        DatePicker::make('next_review_at')
                            ->label('Prossima riverifica'),
                        Textarea::make('gap_notes')
                            ->label('Note sul gap')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
