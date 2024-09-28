<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NilaiDewasaResource\Pages;
use App\Filament\Resources\NilaiDewasaResource\RelationManagers;
use App\Models\NilaiDewasa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Get;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NilaiDewasaResource extends Resource
{
    protected static ?string $model = NilaiDewasa::class;

    protected static ?int $navigationSort = 104;

    protected static ?string $navigationGroup = 'Penilaian';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Split::make([
                    Section::make([
                        TextInput::make('peserta_id')
                            // ->relationship('peserta', 'nama')
                            ->live(onBlur: true)
                            ->disabled()
                            ->formatStateUsing(fn(NilaiDewasa $record): string => $record->peserta->nama ?? ''),
                        TextInput::make('tajwid')
                            ->numeric()
                            ->default(0)
                            ->live(onBlur: true)
                            ->maxValue(30)
                            ->helperText(new HtmlString('<strong>Petunjuk :</strong> Input nilai maksimal 30'))
                            ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                if ($state > 30) {
                                    Notification::make()
                                        ->title(('Nilai melebihi batas maksimum'))
                                        ->danger()
                                        ->body('Maksimal nilai untuk Tajwid adalah 30')
                                        ->send();
                                }
                                $lagu = floatval($get('lagu'));
                                $fashahah = floatval($get('fashahah'));
                                $suara = floatval($get('suara'));
                                $total = floatval($state) + $lagu + $fashahah + $suara;
                                $set('total', floatval($total));
                            }),

                        TextInput::make('lagu')
                            ->numeric()
                            ->default(0)
                            ->live(onBlur: true)
                            ->maxValue(25)
                            ->helperText(new HtmlString('<strong>Petunjuk :</strong> Input nilai maksimal 25'))
                            ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                if ($state > 25) {
                                    Notification::make()
                                        ->title(('Nilai melebihi batas maksimum'))
                                        ->danger()
                                        ->body('Maksimal nilai untuk Irama dan Suara adalah 25')
                                        ->send();
                                }
                                $tajwid = floatval($get('tajwid'));
                                $fashahah = floatval($get('fashahah'));
                                $suara = floatval($get('suara'));
                                $total = floatval($state) + $tajwid + $fashahah + $suara;
                                $set('total', floatval($total));
                            }),

                        TextInput::make('fashahah')
                            ->numeric()
                            ->default(0)
                            ->live(onBlur: true)
                            ->maxValue(30)
                            ->helperText(new HtmlString('<strong>Petunjuk :</strong> Input nilai maksimal 30'))
                            ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                if ($state > 30) {
                                    Notification::make()
                                        ->title(('Nilai melebihi batas maksimum'))
                                        ->danger()
                                        ->body('Maksimal nilai untuk Fashahah adalah 30')
                                        ->send();
                                }
                                $tajwid = floatval($get('tajwid'));
                                $lagu = floatval($get('lagu'));
                                $suara = floatval($get('suara'));
                                $total = floatval($state) + $tajwid + $lagu + $suara;
                                $set('total', floatval($total));
                            }),

                        TextInput::make('suara')
                            ->numeric()
                            ->default(0)
                            ->live(onBlur: true)
                            ->maxValue(15)
                            ->helperText(new HtmlString('<strong>Petunjuk :</strong> Input nilai maksimal 15'))
                            ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                if ($state > 15) {
                                    Notification::make()
                                        ->title(('Nilai melebihi batas maksimum'))
                                        ->danger()
                                        ->body('Maksimal nilai untuk Fashahah adalah 15')
                                        ->send();
                                }
                                $tajwid = floatval($get('tajwid'));
                                $lagu = floatval($get('lagu'));
                                $fashahah = floatval($get('fashahah'));
                                $total = floatval($state) + $tajwid + $lagu + $fashahah;
                                $set('total', floatval($total));
                            }),
                    ]),
                    Section::make([
                        TextInput::make('total')
                            ->numeric()
                            ->readOnly()
                            ->live(onBlur: true)
                            ->reactive(),
                    ]),
                ])
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
                TextColumn::make('lagu'),
                TextColumn::make('fashahah'),
                TextColumn::make('suara'),
                TextColumn::make('total'),
            ])
            ->defaultSort('final_bobot', 'desc')
            ->filters([

            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Input Nilai')
                    ->after(function ($data, $record) {
                        $record->bobot_total = $record->total * 100000000;
                        $record->bobot_tajwid = $record->tajwid * 1000000;
                        $record->bobot_lagu = $record->lagu * 10000;
                        $record->bobot_fashahah = $record->fashahah * 100;
                        $record->final_bobot = $record->bobot_tajwid + $record->bobot_lagu + $record->bobot_fashahah + $record->bobot_total;
                        $record->save();
                    })
                    ->modalHeading('Input Nilai')
                    ->modalDescription('Pastikan input nilai sudah sesuai, karena tidak bisa diubah'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageNilaiDewasas::route('/'),
        ];
    }
}
