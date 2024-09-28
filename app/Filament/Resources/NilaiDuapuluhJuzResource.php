<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NilaiDuapuluhJuzResource\Pages;
use App\Filament\Resources\NilaiDuapuluhJuzResource\RelationManagers;
use App\Models\NilaiDuapuluhJuz;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Get;

class NilaiDuapuluhJuzResource extends Resource
{
    protected static ?string $model = NilaiDuapuluhJuz::class;

    protected static ?int $navigationSort = 108;

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
                            ->formatStateUsing(fn(NilaiDuapuluhJuz $record): string => $record->peserta->nama ?? ''),
                        TextInput::make('tahfizh')
                            ->numeric()
                            ->default(0)
                            ->live(onBlur: true)
                            ->maxValue(50)
                            ->helperText(new HtmlString('<strong>Petunjuk :</strong> Input nilai maksimal 50'))
                            ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                if ($state > 50) {
                                    Notification::make()
                                        ->title(('Nilai melebihi batas maksimum'))
                                        ->danger()
                                        ->body('Maksimal nilai untuk tahfizh adalah 50')
                                        ->send();
                                }
                                $tajwid = floatval($get('tajwid'));
                                $fashahah = floatval($get('fashahah'));
                                $total = floatval($state) + $tajwid + $fashahah;
                                $set('total', floatval($total));
                            }),
                        TextInput::make('tajwid')
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
                                $tahfizh = floatval($get('tahfizh'));
                                $fashahah = floatval($get('fashahah'));
                                $total = floatval($state) + $tahfizh + $fashahah;
                                $set('total', floatval($total));
                            }),
                        TextInput::make('fashahah')
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
                                        ->body('Maksimal nilai untuk Fashahah adalah 25')
                                        ->send();
                                }
                                $tahfizh = floatval($get('tahfizh'));
                                $tajwid = floatval($get('tajwid'));
                                $total = floatval($state) + $tahfizh + $tajwid;
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
                TextColumn::make('tahfizh'),
                TextColumn::make('tajwid'),
                TextColumn::make('fashahah'),
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
                        $record->bobot_tahfizh = $record->tahfizh * 1000000;
                        $record->bobot_tajwid = $record->tajwid * 10000;
                        $record->bobot_fashahah = $record->fashahah * 100;
                        $record->final_bobot = $record->bobot_tahfizh + $record->bobot_tajwid + $record->bobot_fashahah + $record->bobot_total;
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
            'index' => Pages\ManageNilaiDuapuluhJuzs::route('/'),
        ];
    }
}
