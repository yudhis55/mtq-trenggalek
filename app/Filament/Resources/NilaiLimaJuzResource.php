<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\NilaiLimaJuz;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use App\Filament\Resources\NilaiLimaJuzResource\Pages;
use App\Filament\Resources\NilaiLimaJuzResource\RelationManagers;

class NilaiLimaJuzResource extends Resource
{
    protected static ?string $model = NilaiLimaJuz::class;

    protected static ?int $navigationSort = 106;

    protected static ?string $navigationGroup = 'Penilaian';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('peserta_id')
                // ->relationship('peserta', 'nama')
                ->live(onBlur: true)
                ->disabled()
                ->formatStateUsing(fn(NilaiLimaJuz $record): string => $record->peserta->nama ?? ''),
                Split::make([
                    Section::make([
                        TextInput::make('til_tajwid')
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
                                        ->body('Maksimal nilai untuk til_tajwid adalah 30')
                                        ->send();
                                }
                                $til_lagu = floatval($get('til_lagu'));
                                $til_suara = floatval($get('til_suara'));
                                $til_fashahah = floatval($get('til_fashahah'));
                                $total_tilawah = floatval($state) + $til_lagu + $til_suara + $til_fashahah;
                                $set('total_tilawah', floatval($total_tilawah));
                            }),

                        TextInput::make('til_lagu')
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
                                        ->body('Maksimal nilai untuk Irama dan til_fashahah adalah 25')
                                        ->send();
                                }
                                $til_tajwid = floatval($get('til_tajwid'));
                                $til_suara = floatval($get('til_suara'));
                                $til_fashahah = floatval($get('til_fashahah'));
                                $total_tilawah = floatval($state) + $til_tajwid + $til_suara + $til_fashahah;
                                $set('total_tilawah', floatval($total_tilawah));
                            }),

                        TextInput::make('til_suara')
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
                                        ->body('Maksimal nilai untuk til_suara adalah 15')
                                        ->send();
                                }
                                $til_tajwid = floatval($get('til_tajwid'));
                                $til_lagu = floatval($get('til_lagu'));
                                $til_fashahah = floatval($get('til_fashahah'));
                                $total_tilawah = floatval($state) + $til_tajwid + $til_lagu + $til_fashahah;
                                $set('total_tilawah', floatval($total_tilawah));
                            }),

                        TextInput::make('til_fashahah')
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
                                        ->body('Maksimal nilai untuk til_suara adalah 30')
                                        ->send();
                                }
                                $til_tajwid = floatval($get('til_tajwid'));
                                $til_lagu = floatval($get('til_lagu'));
                                $til_suara = floatval($get('til_suara'));
                                $total_tilawah = floatval($state) + $til_tajwid + $til_lagu + $til_suara;
                                $set('total_tilawah', floatval($total_tilawah));
                            }),
                    ]),



                    //PERBATASAN GUYS






                    Section::make([
                    TextInput::make('tah_tahfizh')
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
                                        ->body('Maksimal nilai untuk tah_tahfizh adalah 50')
                                        ->send();
                                }
                                $tah_tajwid = floatval($get('tah_tajwid'));
                                $tah_fashahah = floatval($get('tah_fashahah'));
                                $total_tahfizh = floatval($state) + $tah_tajwid + $tah_fashahah;
                                $set('total_tahfizh', floatval($total_tahfizh));
                            }),
                        TextInput::make('tah_tajwid')
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
                                $tah_tahfizh = floatval($get('tah_tahfizh'));
                                $tah_fashahah = floatval($get('tah_fashahah'));
                                $total_tahfizh = floatval($state) + $tah_tahfizh + $tah_fashahah;
                                $set('total_tahfizh', floatval($total_tahfizh));
                            }),
                        TextInput::make('tah_fashahah')
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
                                        ->body('Maksimal nilai untuk tah_fashahah adalah 25')
                                        ->send();
                                }
                                $tah_tahfizh = floatval($get('tah_tahfizh'));
                                $tah_tajwid = floatval($get('tah_tajwid'));
                                $total_tahfizh = floatval($state) + $tah_tahfizh + $tah_tajwid;
                                $set('total_tahfizh', floatval($total_tahfizh));
                            }),

                        ]),

                    Section::make([
                        TextInput::make('total_tilawah')
                            ->numeric()
                            ->readOnly()
                            ->live(onBlur: true)
                            ->reactive(),
                    ]),
                    Section::make([
                        TextInput::make('total_tahfizh')
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
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('peserta.jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->formatStateUsing(fn (NilaiLimaJuz $record): string => $record->peserta->jenis_kelamin == 'putra' ? 'L' : 'P'),
                TextColumn::make('peserta.utusan.kecamatan'),
                TextColumn::make('til_tajwid'),
                TextColumn::make('til_lagu'),
                TextColumn::make('til_suara'),
                TextColumn::make('til_fashahah'),
                TextColumn::make('tah_tahfizh'),
                TextColumn::make('tah_tajwid'),
                TextColumn::make('tah_fashahah'),
                TextColumn::make('total_tilawah'),
                TextColumn::make('total_tahfizh'),
                TextColumn::make('total'),
            ])
            ->defaultSort('final_bobot', 'desc')
            ->filters([
                SelectFilter::make('peserta.jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'putra' => 'Laki-laki',
                        'putri' => 'Perempuan',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? null;

                        if ($value === 'putra') {
                            // Filter peserta dengan cabang Tartil Putra atau Tartil Putri
                            return $query->whereHas('peserta', function (Builder $query) {
                                $query->where('jenis_kelamin', 'like', '%putra%');
                            });
                        } elseif ($value === 'putri') {
                            // Filter peserta dengan cabang Tilawah Anak-anak Putra atau Tilawah Anak-anak Putri
                            return $query->whereHas('peserta', function (Builder $query) {
                                $query->where('jenis_kelamin', 'like', '%putri%');
                            });
                        }
                    }),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label(__('Download Excel'))
                    ->color('success')
                    ->exports([
                        ExcelExport::make()->fromTable()->except([
                            'index',
                        ]),
                    ])
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Input Nilai')
                    ->after(function ($data, $record) {
                        $record->bobot_total = $record->total * 100000000;
                        $record->bobot_total_tahfizh = $record->total_tahfizh * 1000000;
                        $record->bobot_til_tajwid = $record->til_tajwid * 10000;
                        $record->bobot_tah_tahfizh = $record->tah_tahfizh * 100;
                        $record->final_bobot = $record->bobot_total + $record->bobot_til_tajwid + $record->bobot_tah_tahfizh + $record->bobot_total_tahfizh;
                        $record->total = $record->total_tilawah + $record->total_tahfizh;
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
            'index' => Pages\ManageNilaiLimaJuzs::route('/'),
        ];
    }
}
