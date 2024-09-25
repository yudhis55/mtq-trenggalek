<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\NilaiTartil;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\NilaiTartilResource\Pages;
use App\Filament\Resources\NilaiTartilResource\RelationManagers;
use Filament\Forms\Components\Section;

class NilaiTartilResource extends Resource
{
    protected static ?string $model = NilaiTartil::class;

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationGroup = 'Penilaian';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    Select::make('peserta_id')
                        ->relationship('peserta', 'nama')
                        ->live(onBlur: true)
                        ->disabled(),
                    TextInput::make('tajwid')
                        ->numeric()
                        ->default(0)
                        ->live(onBlur: true)
                        ->maxValue(40)
                        ->helperText(new HtmlString('<strong>Petunjuk :</strong> Input nilai maksimal 40'))
                        ->afterStateUpdated(function ($state, callable $set, Get $get) {
                            $state = (int) $state;
                            if ($state > 40) {
                                Notification::make()
                                    ->title(__('Nilai melebihi batas maksimum'))
                                    ->danger()
                                    ->body('Maksimal nilai untuk Tajwid adalah 40')
                                    ->send();
                            }
                            $set('total', $get('irama_dan_suara') + $get('fashahah') + $state);
                        }),
                    TextInput::make('irama_dan_suara')
                        ->numeric()
                        ->default(0)
                        ->live(onBlur: true)
                        ->maxValue(30)
                        ->helperText(new HtmlString('<strong>Petunjuk :</strong> Input nilai maksimal 30'))
                        ->afterStateUpdated(function ($state, callable $set, Get $get) {
                            $state = (int) $state;
                            if ($state > 30) {
                                Notification::make()
                                    ->title(__('Nilai melebihi batas maksimum'))
                                    ->danger()
                                    ->body('Maksimal nilai untuk Irama dan Suara adalah 30')
                                    ->send();
                            }
                            $set('total', $get('tajwid') + $state + $get('fashahah'));
                        }),
                    TextInput::make('fashahah')
                        ->numeric()
                        ->default(0)
                        ->live(onBlur: true)
                        ->maxValue(30)
                        ->helperText(new HtmlString('<strong>Petunjuk :</strong> Input nilai maksimal 30'))
                        ->afterStateUpdated(function ($state, callable $set, Get $get) {
                            $state = (int) $state;
                            if ($state > 30) {
                                Notification::make()
                                    ->title(__('Nilai melebihi batas maksimum'))
                                    ->danger()
                                    ->body('Maksimal nilai untuk Fashahah adalah 30')
                                    ->send();
                            }
                            $set('total', $get('tajwid') + $get('irama_dan_suara') + $state);
                        }),
                ]),
                Section::make([
                    TextInput::make('total')
                        ->numeric()
                        ->readOnly()
                        ->live()
                        ->reactive(),
                ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('peserta.nama')
                    ->label('Nama'),
                TextColumn::make('peserta.jenis_kelamin')
                    ->label('Jenis Kelamin'),
                TextColumn::make('peserta.utusan.kecamatan'),
                TextColumn::make('tajwid'),
                TextColumn::make('irama_dan_suara'),
                TextColumn::make('fashahah'),
                TextColumn::make('total'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Input Nilai'),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageNilaiTartils::route('/'),
        ];
    }
}
