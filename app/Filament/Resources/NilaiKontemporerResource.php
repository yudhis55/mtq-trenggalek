<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NilaiKontemporerResource\Pages;
use App\Filament\Resources\NilaiKontemporerResource\RelationManagers;
use App\Models\NilaiKontemporer;
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

class NilaiKontemporerResource extends Resource
{
    protected static ?string $model = NilaiKontemporer::class;

    protected static ?int $navigationSort = 115;

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
                            ->formatStateUsing(fn(NilaiKontemporer $record): string => $record->peserta->nama ?? ''),
                        TextInput::make('unsur_kaligrafi')
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
                                        ->body('Maksimal nilai untuk unsur_kaligrafi adalah 30')
                                        ->send();
                                }
                                $unsur_seni_rupa = floatval($get('unsur_seni_rupa'));
                                $sentuhan_akhir = floatval($get('sentuhan_akhir'));
                                $total = floatval($state) + $unsur_seni_rupa + $sentuhan_akhir;
                                $set('total', floatval($total));
                            }),
                        TextInput::make('unsur_seni_rupa')
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
                                        ->body('Maksimal nilai untuk Irama dan Suara adalah 50')
                                        ->send();
                                }
                                $unsur_kaligrafi = floatval($get('unsur_kaligrafi'));
                                $sentuhan_akhir = floatval($get('sentuhan_akhir'));
                                $total = floatval($state) + $unsur_kaligrafi + $sentuhan_akhir;
                                $set('total', floatval($total));
                            }),
                        TextInput::make('sentuhan_akhir')
                            ->numeric()
                            ->default(0)
                            ->live(onBlur: true)
                            ->maxValue(20)
                            ->helperText(new HtmlString('<strong>Petunjuk :</strong> Input nilai maksimal 20'))
                            ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                if ($state > 20) {
                                    Notification::make()
                                        ->title(('Nilai melebihi batas maksimum'))
                                        ->danger()
                                        ->body('Maksimal nilai untuk sentuhan_akhir adalah 20')
                                        ->send();
                                }
                                $unsur_kaligrafi = floatval($get('unsur_kaligrafi'));
                                $unsur_seni_rupa = floatval($get('unsur_seni_rupa'));
                                $total = floatval($state) + $unsur_kaligrafi + $unsur_seni_rupa;
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
                TextColumn::make('unsur_kaligrafi'),
                TextColumn::make('unsur_seni_rupa'),
                TextColumn::make('sentuhan_akhir'),
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
                        $record->bobot_unsur_kaligrafi = $record->unsur_kaligrafi * 1000000;
                        $record->bobot_unsur_seni_rupa = $record->unsur_seni_rupa * 10000;
                        $record->bobot_sentuhan_akhir = $record->sentuhan_akhir * 100;
                        $record->final_bobot = $record->bobot_unsur_kaligrafi + $record->bobot_unsur_seni_rupa + $record->bobot_sentuhan_akhir + $record->bobot_total;
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
            'index' => Pages\ManageNilaiKontemporers::route('/'),
        ];
    }
}
