<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PesertaResource\Pages;
use App\Filament\Resources\PesertaResource\RelationManagers;
use App\Models\Peserta;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Support\Collection;
use App\Models\Cabang;
use Filament\Forms\Get;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use PhpParser\Node\Stmt\Label;

class PesertaResource extends Resource
{
    protected static ?string $model = Peserta::class;

    protected static ?string $navigationIcon = 'heroicon-s-user-group';

    protected static ?string $navigationLabel = 'Peserta';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->label(__('Nama Lengkap'))
                    ->required()
                    ->maxLength(255)
                    ->validationMessages([
                        'required' => 'Kolom Nama tidak boleh kosong',
                    ]),
                Forms\Components\Select::make('jenis_kelamin')
                    ->label(__('Jenis Kelamin'))
                    ->required()
                    ->options([
                        'putra' => 'Laki-Laki',
                        'putri' => 'Perempuan',
                    ])
                    ->native(false)
                    ->live()
                    ->validationMessages([
                        'required' => 'Kolom Jenis Kelamin tidak boleh kosong',
                    ])
                    ->afterStateUpdated(function ($state, $get, $set) {
                        $set('cabang_id', null);
                    }),
                Forms\Components\TextInput::make('nik')
                    ->label(__('NIK'))
                    ->required()
                    ->numeric()
                    ->length(16)
                    ->unique(column: 'nik')
                    ->validationMessages([
                        'required' => 'Kolom NIK tidak boleh kosong',
                        'unique' => 'NIK sudah pernah didaftarkan.',
                    ]),
                Forms\Components\TextInput::make('tempat_lahir')
                    ->label(__('Tempat Lahir'))
                    ->required()
                    ->maxLength(50)
                    ->validationMessages([
                        'required' => 'Kolom Tempat Lahir tidak boleh kosong',
                    ]),
                Forms\Components\DatePicker::make('tgl_lahir')
                    ->label(__('Tanggal Lahir'))
                    ->required()
                    ->native(false)
                    ->closeOnDateSelection()
                    ->format('d-m-Y')
                    ->displayFormat('d/m/Y')
                    ->live()
                    ->afterStateUpdated(function ($state, $get, $set) {
                        // Kosongkan field cabang_id jika tanggal lahir diubah
                        $set('cabang_id', null);
                    })
                    ->validationMessages([
                        'required' => 'Kolom Tanggal Lahir tidak boleh kosong',
                    ]),
                Forms\Components\Textarea::make('alamat_ktp')
                    ->label(__('Alamat KTP'))
                    ->required()
                    ->maxLength(500)
                    ->validationMessages([
                        'required' => 'Kolom Alamat KTP tidak boleh kosong',
                    ]),
                Forms\Components\Select::make('utusan_id')
                    ->label(__('Utusan Kecamatan'))
                    ->required()
                    ->relationship('utusan', 'kecamatan')
                    ->native(false)
                    ->validationMessages([
                        'required' => 'Kolom Utusan Kecamatan tidak boleh kosong',
                    ]),
                Forms\Components\Select::make('cabang_id')
                    ->required(fn(Get $get): bool => filled($get('tgl_lahir')))
                    ->label(__('Cabang yang Diikuti'))
                    ->required()
                    ->live()
                    // ->relationship('cabang', 'nama_cabang')
                    ->options(fn(Get $get): Collection => Cabang::query()
                        ->where('gender_cabang', $get('jenis_kelamin'))
                        ->pluck('nama_cabang', 'id'))
                    ->native(false)
                    ->afterStateUpdated(function ($state, $get, $set) {
                        $birthdate = $get('tgl_lahir');
                        if (!$birthdate) {
                            // Tanggal lahir belum dipilih, reset cabang_id dan beri notifikasi
                            $set('cabang_id', null);
                            Notification::make()
                                ->title(__('Silakan mengisi tanggal lahir terlebih dahulu sebelum memilih cabang.'))
                                ->danger()
                                ->send();
                            return;
                        }

                        $cabang = Cabang::find($state);
                        if ($cabang) {
                            $perTanggal = Carbon::parse($cabang->per_tanggal);
                            $ageInDays = Carbon::parse($birthdate)->diffInDays($perTanggal);

                            // Parse batas_umur (e.g., '10 tahun 11 bulan 29 hari')
                            $batasUmur = self::parseAgeString($cabang->batas_umur);
                            $maxAgeDate = $perTanggal->copy()
                                ->subYears($batasUmur['years'])
                                ->subMonths($batasUmur['months'])
                                ->subDays($batasUmur['days']);
                            $maxAgeInDays = $maxAgeDate->diffInDays($perTanggal);

                            if ($ageInDays > $maxAgeInDays) {
                                $set('cabang_id', null);
                                Notification::make()
                                    ->title(__('Usia peserta melebihi batas maksimal untuk cabang ini.'))
                                    ->danger()
                                    ->send();
                            }
                        }
                    })
                    ->reactive()
                    ->validationMessages([
                        'required' => 'Kolom Cabang yang Diikuti tidak boleh kosong',
                    ]),
                FileUpload::make('kk_ktp')
                    ->label(__('Foto KK / KTP'))
                    ->required()
                    ->openable()
                    ->image()
                    ->maxSize(512)
                    ->validationMessages([
                        'required' => 'File KK / KTP belum diunggah',
                    ])
                    ->helperText(new HtmlString('<strong>Petunjuk :</strong> Unggah foto berukuran maksimal 512kb.'))
                    ->moveFiles(),
                // FileUpload::make('akta')
                //     ->label(__('Akta Kelahiran'))
                //     ->required()
                //     ->openable()
                //     ->image()
                //     ->maxSize(512)
                //     ->validationMessages([
                //         'required' => 'File Akta Kelahiran belum diunggah',
                //     ])
                //     ->moveFiles(),
                // FileUpload::make('ijazah')
                //     ->label(__('Ijazah Terakhir'))
                //     ->required()
                //     ->image()
                //     ->openable()
                //     ->maxSize(512)
                //     ->validationMessages([
                //         'required' => 'File Ijazah Terakhir belum diunggah',
                //     ])
                //     ->moveFiles(),
                // FileUpload::make('piagam')
                //     ->label(__('Piagam'))
                //     ->required()
                //     ->image()
                //     ->openable()
                //     ->maxSize(512)
                //     ->validationMessages([
                //         'required' => 'File Piagam belum diunggah',
                //     ])
                //     ->moveFiles(),
                // FileUpload::make('pasfoto')
                //     ->label(__('Pas Foto'))
                //     ->required()
                //     ->image()
                //     ->openable()
                //     ->maxSize(512)
                //     ->validationMessages([
                //         'required' => 'File Pas Foto belum diunggah',
                //     ])
                //     ->moveFiles(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->limit(15)
                    ->label(__('Nama')),
                Tables\Columns\TextColumn::make('tempat_lahir')
                    ->label(__('Tempat Lahir')),
                Tables\Columns\TextColumn::make('tgl_lahir')
                    ->label(__('Tgl Lahir')),
                Tables\Columns\TextColumn::make('alamat_ktp')
                    ->limit(20)
                    ->label(__('Alamat KTP')),
                Tables\Columns\TextColumn::make('utusan.kecamatan')
                    ->label(__('Utusan')),
                Tables\Columns\TextColumn::make('cabang.nama_cabang')
                    ->label(__('Cabang')),
            ])
            ->filters([
                SelectFilter::make('utusan.kecamatan')
                    ->relationship('utusan', 'kecamatan')
                    ->label(__('Kecamatan'))
                    ->native(false),
                SelectFilter::make('cabang.nama_cabang')
                    ->relationship('cabang', 'nama_cabang')
                    ->label(__('Cabang'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPesertas::route('/'),
            'create' => Pages\CreatePeserta::route('/create'),
            'edit' => Pages\EditPeserta::route('/{record}/edit'),
        ];
    }

    protected static function parseAgeString($ageString)
    {
        preg_match('/(?:(\d+)\s*tahun)?\s*(?:(\d+)\s*bulan)?\s*(?:(\d+)\s*hari)?/', $ageString, $matches);

        return [
            'years' => (int) ($matches[1] ?? 0),
            'months' => (int) ($matches[2] ?? 0),
            'days' => (int) ($matches[3] ?? 0),
        ];
    }
}
