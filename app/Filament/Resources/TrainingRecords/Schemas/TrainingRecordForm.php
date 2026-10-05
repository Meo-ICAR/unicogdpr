<?php

namespace App\Filament\Resources\TrainingRecords\Schemas;

use App\Models\Employee;
use App\Models\ExternalProcessor;
use App\Models\TrainingCourse;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class TrainingRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── Sezione 1: Partecipante ──────────────────────────────────────
                Section::make('Partecipante')
                    ->icon('heroicon-o-user')
                    ->schema([
                        MorphToSelect::make('ownerable')
                            ->label('Soggetto Formato')
                            ->types([
                                MorphToSelect\Type::make(Employee::class)
                                    ->titleAttribute('first_name')
                                    ->label('Dipendente / Collaboratore'),
                                MorphToSelect\Type::make(ExternalProcessor::class)
                                    ->titleAttribute('name')
                                    ->label('Responsabile Esterno del Trattamento'),
                            ])
                            ->searchable()
                            ->preload(),
                        // ->nullable(),
                    ]),

                // ── Sezione 2: Corso ─────────────────────────────────────────────
                Section::make('Dati Corso di Formazione')
                    ->icon('heroicon-o-academic-cap')
                    ->columns(2)
                    ->schema([
                        Select::make('training_course_id')
                            ->label('Corso dal catalogo')
                            ->relationship('trainingCourse', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->helperText('Facoltativo: precompila i campi sottostanti dal catalogo corsi, restano comunque modificabili.')
                            ->afterStateUpdated(function (?string $state, Set $set) {
                                $course = $state ? TrainingCourse::find($state) : null;

                                if (! $course) {
                                    return;
                                }

                                $set('course_name', $course->name);
                                $set('course_description', $course->description);
                                $set('provider', $course->provider);
                                $set('trainer', $course->trainer);
                                $set('delivery_mode', $course->delivery_mode);
                                $set('hours', $course->default_hours);
                            })
                            ->columnSpanFull(),
                        TextInput::make('course_name')
                            ->label('Titolo del Corso')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Es. Corso GDPR Base & Cybersecurity per Dipendenti')
                            ->columnSpanFull(),
                        // BelongsTo lookup inline per modalità erogazione
                        Select::make('delivery_mode')
                            ->label('Modalità di Erogazione')
                            ->required()
                            ->options([
                                'in_person' => '🏫 In Presenza / Aula',
                                'online' => '💻 E-Learning / FAD Asincrona',
                                'blended' => '🔀 Misto / Blended',
                                'on_the_job' => '🛠️ On The Job / Affiancamento',
                                'webinar' => '📹 Webinar / Aula Virtuale',
                            ])
                            ->default('in_person'),
                        TextInput::make('provider')
                            ->label('Ente Erogatore / Provider')
                            ->maxLength(255)
                            ->placeholder('Es. GDPR Academy Srl, Consulente Privacy'),
                        TextInput::make('trainer')
                            ->label('Formatore / Docente')
                            ->maxLength(255)
                            ->placeholder('Es. Avv. Mario Rossi (DPO)'),
                        TextInput::make('hours')
                            ->label('Durata (Ore)')
                            ->numeric()
                            ->default(4),
                        Textarea::make('course_description')
                            ->label('Descrizione e Programma')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                // ── Sezione 3: Esito e Certificazione ───────────────────────────
                Section::make('Date, Esito e Certificazione')
                    ->icon('heroicon-o-check-badge')
                    ->columns(3)
                    ->schema([
                        DatePicker::make('training_date')
                            ->label('Data Svolgimento')
                            ->required()
                            ->default(now()),
                        DatePicker::make('expiry_date')
                            ->label('Scadenza Validità')
                            ->helperText('Solitamente ogni 12/24 mesi'),
                        // BelongsTo lookup inline per esito
                        Select::make('outcome')
                            ->label('Esito Finale')
                            ->options([
                                'passed' => '✅ Superato / Idoneo',
                                'failed' => '❌ Non Superato',
                                'attended' => '📋 Solo Frequenza',
                            ])
                            ->default('passed'),
                        TextInput::make('score')
                            ->label('Punteggio Test (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        Toggle::make('certificate_issued')
                            ->label('Attestato Rilasciato')
                            ->default(true),
                        TextInput::make('certificate_number')
                            ->label('Numero Attestato')
                            ->maxLength(255),
                        SpatieMediaLibraryFileUpload::make('certificate')
                            ->label('Allegato attestato')
                            ->collection('certificates')
                            ->disk('documenti')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->columnSpanFull(),
                    ]),

                // ── Sezione 4: Note ──────────────────────────────────────────────
                Section::make('Note')
                    ->icon('heroicon-o-pencil-square')
                    ->collapsed()
                    ->schema([
                        Textarea::make('notes')
                            ->label('Note Operative')
                            ->rows(3),
                    ]),
            ]);
    }
}
