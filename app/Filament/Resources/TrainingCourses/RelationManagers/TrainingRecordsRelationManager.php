<?php

namespace App\Filament\Resources\TrainingCourses\RelationManagers;

use App\Models\Employee;
use App\Models\ExternalProcessor;
use App\Models\TrainingCourse;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TrainingRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'trainingRecords';

    protected static ?string $title = 'Sessioni Svolte';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                MorphToSelect::make('ownerable')
                    ->label('Partecipante')
                    ->types([
                        MorphToSelect\Type::make(Employee::class)
                            ->titleAttribute('first_name')
                            ->label('Dipendente / Collaboratore'),
                        MorphToSelect\Type::make(ExternalProcessor::class)
                            ->titleAttribute('name')
                            ->label('Responsabile Esterno del Trattamento'),
                    ])
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
                DatePicker::make('training_date')
                    ->label('Data Svolgimento')
                    ->required()
                    ->default(now()),
                TextInput::make('hours')
                    ->label('Ore')
                    ->numeric(),
                Select::make('outcome')
                    ->label('Esito')
                    ->options([
                        'passed' => 'Superato',
                        'failed' => 'Non superato',
                        'attended' => 'Frequentato',
                    ])
                    ->default('passed'),
                DatePicker::make('expiry_date')
                    ->label('Scadenza Validità'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('course_name')
            ->modifyQueryUsing(fn ($query) => $query->with('ownerable'))
            ->columns([
                TextColumn::make('ownerable.name')
                    ->label('Partecipante')
                    ->getStateUsing(fn ($record) => $record->ownerable?->name
                        ?? trim(($record->ownerable?->first_name ?? '').' '.($record->ownerable?->last_name ?? ''))
                        ?: '—'),
                TextColumn::make('training_date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('hours')
                    ->label('Ore')
                    ->numeric(),
                TextColumn::make('outcome')
                    ->label('Esito')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'passed' => 'Superato',
                        'failed' => 'Non superato',
                        'attended' => 'Frequentato',
                        default => $state,
                    }),
                IconColumn::make('certificate_issued')
                    ->label('Attestato')
                    ->boolean(),
                TextColumn::make('expiry_date')
                    ->label('Scadenza')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $record?->expiry_date?->isPast() ? 'danger' : null),
            ])
            ->filters([
                SelectFilter::make('outcome')
                    ->label('Esito')
                    ->options([
                        'passed' => 'Superato',
                        'failed' => 'Non superato',
                        'attended' => 'Frequentato',
                    ]),
            ])
            ->headerActions([
                Action::make('nuovo')
                    ->label('Nuovo')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Registra sessione formativa')
                    ->form(function () {
                        /** @var TrainingCourse $course */
                        $course = $this->getOwnerRecord();

                        // Dipendenti dell'azienda del corso che non hanno
                        // ancora una sessione registrata per QUESTO corso.
                        $alreadyTrainedIds = $course->trainingRecords()
                            ->where('ownerable_type', 'employee')
                            ->pluck('ownerable_id');

                        $availableEmployees = Employee::where('company_id', $course->company_id)
                            ->whereNotIn('id', $alreadyTrainedIds)
                            ->orderBy('first_name')
                            ->get()
                            ->mapWithKeys(fn (Employee $e) => [$e->id => "{$e->first_name} {$e->last_name}"]);

                        return [
                            CheckboxList::make('employee_ids')
                                ->label('Dipendenti')
                                ->options($availableEmployees)
                                ->bulkToggleable()
                                ->searchable()
                                ->required()
                                ->helperText('I dipendenti che hanno già svolto questo corso non sono elencati.')
                                ->columns(2)
                                ->columnSpanFull(),
                            DatePicker::make('training_date')
                                ->label('Data Svolgimento')
                                ->required()
                                ->default(now()),
                            TextInput::make('hours')
                                ->label('Ore')
                                ->numeric()
                                ->default($course->default_hours),
                            Select::make('outcome')
                                ->label('Esito')
                                ->options([
                                    'passed' => 'Superato',
                                    'failed' => 'Non superato',
                                    'attended' => 'Frequentato',
                                ])
                                ->default('passed'),
                            DatePicker::make('expiry_date')
                                ->label('Scadenza Validità'),
                        ];
                    })
                    ->action(function (array $data): void {
                        /** @var TrainingCourse $course */
                        $course = $this->getOwnerRecord();

                        foreach ($data['employee_ids'] as $employeeId) {
                            $course->trainingRecords()->create([
                                'company_id' => $course->company_id,
                                'ownerable_type' => 'employee',
                                'ownerable_id' => $employeeId,
                                'course_name' => $course->name,
                                'course_description' => $course->description,
                                'provider' => $course->provider,
                                'trainer' => $course->trainer,
                                'delivery_mode' => $course->delivery_mode,
                                'training_date' => $data['training_date'],
                                'hours' => $data['hours'] ?? $course->default_hours,
                                'outcome' => $data['outcome'],
                                'expiry_date' => $data['expiry_date'] ?? null,
                            ]);
                        }

                        Notification::make()
                            ->title('Sessioni registrate')
                            ->body(count($data['employee_ids']).' dipendente/i registrato/i per questo corso.')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
