<?php

namespace App\Filament\Resources\Dpias\Schemas;

use App\Models\Dpia;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class DpiaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([

                    // ── Step 1: Testata DPIA ─────────────────────────────────────
                    Step::make('Identificazione')
                        ->icon('heroicon-o-document-magnifying-glass')
                        ->schema([
                            TextInput::make('name')
                                ->label('Titolo DPIA')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),

                            // BelongsTo → ProcessingActivity (registro canonico Art. 30)
                            Select::make('processing_activity_id')
                                ->label('Trattamento di Riferimento (Art. 30)')
                                ->relationship(name: 'processingActivity', titleAttribute: 'name')
                                ->searchable()
                                ->preload(),

                            Select::make('status')
                                ->label('Stato')
                                ->required()
                                ->default('draft')
                                ->disabled(fn (?Dpia $record) => $record?->isSignedByDpo())
                                ->dehydrated()
                                ->options([
                                    'draft' => '📝 Bozza',
                                    'under_review' => '🔍 In Revisione',
                                    'completed' => '✅ Completata (solo dopo firma DPO)',
                                ]),
                            DatePicker::make('completion_date')
                                ->label('Data Completamento')
                                ->disabled()
                                ->dehydrated(),
                            DatePicker::make('next_review_date')
                                ->label('Prossima Revisione Obbligatoria'),
                        ])
                        ->columns(2),

                    // ── Step 2: Necessità e Proporzionalità ──────────────────────
                    Step::make('Necessità e Proporzionalità')
                        ->icon('heroicon-o-scale')
                        ->schema([
                            Textarea::make('description_of_processing')
                                ->label('Descrizione Sistematica del Trattamento')
                                ->rows(4)
                                ->required()
                                ->columnSpanFull(),
                            Textarea::make('necessity_assessment')
                                ->label('Valutazione di Necessità e Proporzionalità')
                                ->rows(3)
                                ->columnSpanFull(),
                            Toggle::make('is_necessary')
                                ->label('Trattamento Necessario')
                                ->default(true),
                            Toggle::make('is_proportional')
                                ->label('Trattamento Proporzionato')
                                ->default(true),
                        ])
                        ->columns(2),

                    // ── Step 3: Analisi Rischi (Repeater con cataloghi) ──────────
                    Step::make('Analisi dei Rischi')
                        ->icon('heroicon-o-shield-exclamation')
                        ->schema([
                            Repeater::make('items')
                                ->label('')
                                ->relationship()
                                ->schema([
                                    Select::make('dpia_risk_id')
                                        ->label('Tipologia di Rischio (catalogo)')
                                        ->relationship(name: 'riskCatalog', titleAttribute: 'name')
                                        ->searchable()
                                        ->preload()
                                        ->columnSpan(2),
                                    TextInput::make('risk_source')
                                        ->label('Fonte / Dettaglio del Rischio')
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(2),
                                    Select::make('dpia_impact_id')
                                        ->label('Tipologia di Impatto (catalogo)')
                                        ->relationship(name: 'impactCatalog', titleAttribute: 'name')
                                        ->searchable()
                                        ->preload()
                                        ->columnSpan(2),
                                    Textarea::make('potential_impact')
                                        ->label('Impatto Potenziale sugli Interessati')
                                        ->rows(2)
                                        ->columnSpan(2),

                                    Select::make('probability')
                                        ->label('Probabilità (1–5)')
                                        ->options([
                                            1 => '1 — Molto bassa',
                                            2 => '2 — Bassa',
                                            3 => '3 — Media',
                                            4 => '4 — Alta',
                                            5 => '5 — Molto alta',
                                        ])
                                        ->required()
                                        ->default(1)
                                        ->live()
                                        ->afterStateUpdated(fn (Get $get, Set $set) => static::updateRiskScores($get, $set)),

                                    Select::make('severity')
                                        ->label('Gravità (1–5)')
                                        ->options([
                                            1 => '1 — Trascurabile',
                                            2 => '2 — Minore',
                                            3 => '3 — Moderata',
                                            4 => '4 — Grave',
                                            5 => '5 — Molto grave',
                                        ])
                                        ->required()
                                        ->default(1)
                                        ->live()
                                        ->afterStateUpdated(fn (Get $get, Set $set) => static::updateRiskScores($get, $set)),

                                    Select::make('privacy_security_id')
                                        ->label('Misura di Mitigazione Applicata')
                                        ->relationship(name: 'securityMeasure', titleAttribute: 'name')
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->afterStateUpdated(fn (Get $get, Set $set) => static::updateRiskScores($get, $set))
                                        ->columnSpan(2),

                                    TextInput::make('inherent_risk_score')
                                        ->label('Rischio Intrinseco (P×G)')
                                        ->numeric()
                                        ->readOnly()
                                        ->default(1),

                                    TextInput::make('residual_risk_score')
                                        ->label('Rischio Residuo (post mitigazione)')
                                        ->numeric()
                                        ->readOnly()
                                        ->default(1),
                                ])
                                ->columns(2)
                                ->addActionLabel('+ Aggiungi elemento di rischio')
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['risk_source'] ?? 'Nuovo rischio'),
                        ]),

                    // ── Step 4: Parere e Validazione Formale del DPO ─────────────
                    Step::make('Parere del DPO')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->schema([
                            Textarea::make('dpo_opinion')
                                ->label('Parere espresso dal Responsabile della Protezione dei Dati')
                                ->rows(4)
                                ->disabled(fn (?Dpia $record) => $record?->isSignedByDpo())
                                ->dehydrated()
                                ->columnSpanFull(),

                            Section::make('Validazione Formale')
                                ->icon('heroicon-o-shield-check')
                                ->visible(fn (?Dpia $record) => $record?->isSignedByDpo())
                                ->schema([
                                    Placeholder::make('dpo_signed_by_display')
                                        ->label('Validata da')
                                        ->content(fn (?Dpia $record) => $record?->dpoSignedBy?->name ?? '—'),
                                    Placeholder::make('dpo_signed_at_display')
                                        ->label('Data validazione')
                                        ->content(fn (?Dpia $record) => $record?->dpo_signed_at?->format('d/m/Y H:i') ?? '—'),
                                    Placeholder::make('dpo_signature_hash_display')
                                        ->label('Impronta SHA-256 (integrità contenuto)')
                                        ->content(fn (?Dpia $record) => $record?->dpo_signature_hash ?? '—')
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ]),
                ])
                    ->columnSpanFull()
                    ->persistStepInQueryString(),
            ]);
    }

    /**
     * Ricalcola i punteggi di rischio in modo reattivo.
     */
    protected static function updateRiskScores(Get $get, Set $set): void
    {
        $probability = (int) ($get('probability') ?? 1);
        $severity = (int) ($get('severity') ?? 1);
        $hasMitigation = ! empty($get('privacy_security_id'));

        $inherentScore = $probability * $severity;
        $residualScore = (int) ceil($inherentScore * ($hasMitigation ? 0.6 : 1.0));

        $set('inherent_risk_score', $inherentScore);
        $set('residual_risk_score', $residualScore);
    }
}
