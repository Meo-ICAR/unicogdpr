<?php

namespace App\Filament\Resources\ExternalProcessorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class TransferImpactAssessmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'transferImpactAssessments';

    protected static ?string $title = 'TIA (Transfer Impact Assessments)';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make('Informazioni Generali Trasferimento')
                    ->columns(2)
                    ->schema([
                        TextInput::make('destination_country')
                            ->label('Paese di Destinazione')
                            ->required()
                            ->placeholder('Es. Stati Uniti'),

                        Select::make('transfer_mechanism')
                            ->label('Meccanismo di Trasferimento (Capo V GDPR)')
                            ->options([
                                'adequacy_decision' => 'Decisione di Adeguatezza (es. Data Privacy Framework)',
                                'scc' => 'Standard Contractual Clauses (SCC)',
                                'bcr' => 'Binding Corporate Rules (BCR)',
                                'derogation' => 'Deroghe specifiche (Art. 49)',
                            ])
                            ->required(),

                        DatePicker::make('assessment_date')
                            ->label('Data Valutazione')
                            ->default(now())
                            ->required(),

                        DatePicker::make('next_review_date')
                            ->label('Prossima Revisione'),

                        Forms\Components\Toggle::make('fisa_702_applicable')
                            ->label('Soggetto a normative di sorveglianza governativa (es. FISA 702, EO 12333)?')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Misure Supplementari (Raccomandazioni EDPB)')
                    ->description('Da compilare in assenza di Decisione di Adeguatezza o in presenza di rischio sorveglianza.')
                    ->collapsed()
                    ->schema([
                        Textarea::make('technical_measures')
                            ->label('Misure Tecniche')
                            ->placeholder('Es. Dati criptati in transito e a riposo, chiavi crittografiche detenute dal Titolare in UE...'),

                        Textarea::make('organizational_measures')
                            ->label('Misure Organizzative')
                            ->placeholder('Es. Policy interne rigorose, minimizzazione dei dati inviati...'),

                        Textarea::make('contractual_measures')
                            ->label('Misure Contrattuali')
                            ->placeholder('Es. Obbligo di opporsi a richieste di accesso governative, obbligo di notifica al Titolare...'),
                    ]),

                Forms\Components\Section::make('Esito e Documentazione')
                    ->schema([
                        Select::make('result')
                            ->label('Esito TIA')
                            ->options([
                                'approved' => 'Trasferimento Approvato',
                                'approved_with_measures' => 'Approvato con Misure Supplementari',
                                'rejected' => 'Trasferimento Vietato (Sospendere invio dati)',
                            ])
                            ->native(false)
                            ->required(),

                        SpatieMediaLibraryFileUpload::make('documentazione_tia')
                            ->label('Documenti (Es. Certificato DPF, Analisi Legale)')
                            ->collection('tia_documents')
                            ->disk('google')
                            ->multiple()
                            ->downloadable(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('destination_country')
            ->columns([
                Tables\Columns\TextColumn::make('destination_country')
                    ->label('Paese')
                    ->searchable(),

                Tables\Columns\TextColumn::make('transfer_mechanism')
                    ->label('Meccanismo')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'adequacy_decision' => 'Adeguatezza',
                        'scc' => 'SCC',
                        'bcr' => 'BCR',
                        'derogation' => 'Deroga',
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('fisa_702_applicable')
                    ->label('Rischio Sorveglianza')
                    ->boolean(),

                Tables\Columns\TextColumn::make('result')
                    ->label('Esito')
                    ->badge()
                    ->colors([
                        'success' => 'approved',
                        'warning' => 'approved_with_measures',
                        'danger' => 'rejected',
                    ]),

                Tables\Columns\TextColumn::make('assessment_date')
                    ->date()
                    ->label('Data'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
