<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use App\Models\Employee;
use App\Models\TrainingCourse;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TrainingRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'trainingRecords';

    protected static ?string $title = 'Formazione';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('training_course_id')
                    ->label('Corso dal catalogo')
                    ->relationship('trainingCourse', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->helperText('Facoltativo: precompila i campi sottostanti dal catalogo corsi.')
                    ->afterStateUpdated(function (?string $state, Set $set) {
                        $course = $state ? TrainingCourse::find($state) : null;

                        if (! $course) {
                            return;
                        }

                        $set('course_name', $course->name);
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
            ->defaultSort('training_date', 'desc')
            ->columns([
                TextColumn::make('course_name')
                    ->label('Corso')
                    ->searchable()
                    ->weight('bold'),
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
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        /** @var Employee $employee */
                        $employee = $this->getOwnerRecord();

                        $data['company_id'] = $employee->company_id;

                        return $data;
                    }),
            ])
            ->recordActions([
                Action::make('download_certificate')
                    ->label('Attestato')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->visible(fn ($record) => $record->getFirstMedia('certificates') !== null)
                    ->action(function ($record) {
                        $media = $record->getFirstMedia('certificates');

                        return response()->download($media->getPath(), $media->file_name);
                    }),
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
