<?php

namespace App\Filament\Resources\Dpias\Schemas;

use App\Models\PrivacySecurity;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Schema;

class DpiaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informazioni Generali DPIA')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Titolo DPIA')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('registro_trattamenti_item_id')
                            ->label('Trattamento di riferimento (Art. 30)')
                            ->relationship('registroTrattamento', 'activity')
                            ->searchable()
                            ->preload(),
                        Select::make('status')
                            ->label('Stato')
                            ->required()
                            ->default('draft')
                            ->options([
                                'draft'        => '📝 Bozza',
                                'under_review' => '🔍 In revisione',
                                'completed'    => '✅ Completata',
                            ]),
                        DatePicker::make('completion_date')
                            ->label('Data completamento'),
                        DatePicker::make('next_review_date')
                            ->label('Prossima revisione obbligatoria'),
                    ]),

                Section::make('Valutazione Necessità e Proporzionalità')
                    ->icon('heroicon-o-scale')
                    ->columns(2)
                    ->schema([
                        Textarea::make('description_of_processing')
                            ->label('Descrizione sistematica del trattamento')
                            ->rows(4)
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('necessity_assessment')
                            ->label('Valutazione di necessità e proporzionalità')
                            ->rows(3)
                            ->columnSpanFull(),
                        Toggle::make('is_necessary')
                            ->label('Trattamento necessario')
                            ->default(true),
                        Toggle::make('is_proportional')
                            ->label('Trattamento proporzionato')
                            ->default(true),
                    ]),

                Section::make('Analisi dei Rischi')
                    ->icon('heroicon-o-shield-exclamation')
                    ->schema([
                        Repeater::make('items')
                            ->label('Elementi di rischio identificati')
                            ->relationship()
                            ->schema([
                                TextInput::make('risk_source')
                                    ->label('Fonte/Origine del rischio')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),
                                Textarea::make('potential_impact')
                                    ->label('Impatto potenziale sugli interessati')
                                    ->rows(2)
                                    ->columnSpan(2),
                                Select::make('probability')
                                    ->label('Probabilità (1-5)')
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
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        static::updateRiskScores($get, $set);
                                    }),
                                Select::make('severity')
                                    ->label('Gravità (1-5)')
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
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        static::updateRiskScores($get, $set);
                                    }),
                                Select::make('privacy_security_id')
                                    ->label('Misura di mitigazione applicata')
                                    ->options(PrivacySecurity::pluck('name', 'id'))
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        static::updateRiskScores($get, $set);
                                    })
                                    ->columnSpan(2),
                                TextInput::make('inherent_risk_score')
                                    ->label('Rischio Intrinseco (P×G)')
                                    ->numeric()
                                    ->readOnly()
                                    ->default(1)
                                    ->suffixIcon(fn ($state) => match (true) {
                                        $state >= 20 => 'heroicon-o-fire',
                                        $state >= 10 => 'heroicon-o-exclamation-triangle',
                                        default      => 'heroicon-o-check-circle',
                                    })
                                    ->extraAttributes(fn ($state) => [
                                        'class' => match (true) {
                                            $state >= 20 => 'text-red-600 font-bold',
                                            $state >= 10 => 'text-yellow-600 font-bold',
                                            default      => 'text-green-600',
                                        },
                                    ]),
                                TextInput::make('residual_risk_score')
                                    ->label('Rischio Residuo (dopo mitigazione)')
                                    ->numeric()
                                    ->readOnly()
                                    ->default(1),
                            ])
                            ->columns(2)
                            ->addActionLabel('+ Aggiungi elemento di rischio')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['risk_source'] ?? 'Nuovo rischio'),
                    ]),

                Section::make('Parere DPO')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->schema([
                        Textarea::make('dpo_opinion')
                            ->label('Parere espresso dal Responsabile della Protezione dei Dati')
                            ->rows(4),
                    ]),
            ]);
    }

    /**
     * Calcola e aggiorna i punteggi di rischio intrinseco e residuo in modo reattivo.
     */
    protected static function updateRiskScores(Get $get, Set $set): void
    {
        $probability = (int) ($get('probability') ?? 1);
        $severity    = (int) ($get('severity') ?? 1);
        $hasMitigation = ! empty($get('privacy_security_id'));

        $inherentScore = $probability * $severity;
        $residualScore = (int) ceil($inherentScore * ($hasMitigation ? 0.6 : 1.0));

        $set('inherent_risk_score', $inherentScore);
        $set('residual_risk_score', $residualScore);
    }
}
