<?php

namespace App\Filament\Kecamatan\Resources;

use App\Filament\Kecamatan\Resources\PesertaResource\Pages;
use App\Filament\Kecamatan\Resources\PesertaResource\RelationManagers;
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
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Livewire\Attributes\Reactive;
use PhpParser\Node\Stmt\Label;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Section;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use App\Models\Utusan;
use App\Models\Tahun;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\Alignment;

class PesertaResource extends Resource
{
    protected static ?string $model = Peserta::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Daftar Peserta';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('cabang_id')
                    ->label(__('Cabang yang Diikuti'))
                    ->required()
                    ->preload()
                    ->relationship('cabang', 'nama_cabang')
                    ->options(function (Get $get) {
                        $utusanId = $get('utusan_id');
                        $tahunId = $get('tahun_id');

                        if (!$utusanId || !$tahunId) {
                            return [];
                        }

                        return Cabang::query()
                            ->get()
                            ->filter(function ($cabang) use ($utusanId, $tahunId) {
                                // Mengambil kuota dari database
                                $kuota = $cabang->kuota;

                                // Hitung jumlah peserta yang sudah terdaftar untuk utusan dan tahun yang sama
                                $jumlahPeserta = Peserta::where('utusan_id', $utusanId)
                                    ->where('tahun_id', $tahunId)
                                    ->where('cabang_id', $cabang->id)
                                    ->count();

                                return $jumlahPeserta < $kuota;
                            })
                            ->pluck('nama_cabang', 'id');
                    })
                    ->native(false)
                    ->reactive()
                    ->afterStateUpdated(function ($state, $get, $set) {
                        $cabang = Cabang::find($state);
                        if ($cabang) {
                            // Set jenis kelamin berdasarkan gender cabang
                            $set('jenis_kelamin', $cabang->gender_cabang);

                            // Reset tanggal lahir jika cabang berubah
                            $set('tgl_lahir', null);
                        }
                    })
                    ->validationMessages([
                        'required' => 'Kolom Cabang yang Diikuti tidak boleh kosong',
                    ]),
                Forms\Components\TextInput::make('nik')
                    ->label(__('NIK'))
                    ->validationAttribute('NIK')
                    ->required()
                    ->numeric()
                    ->minLength(16)
                    ->maxLength(16)
                    ->live(onBlur: true)
                    ->validationMessages([
                        'required' => 'Kolom NIK tidak boleh kosong',
                        'numeric' => 'NIK harus berupa angka',
                    ])
                    ->rules([
                        fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                            // Validasi panjang NIK
                            if (strlen($value) !== 16) {
                                $fail("NIK harus terdiri dari 16 digit.");
                            }

                            $tahunId = $get('tahun_id');
                            if (!$tahunId) {
                                $fail('Tahun belum dipilih.');
                            }

                            // Periksa apakah ada peserta dengan NIK dan tahun_id yang sama
                            $pesertaExists = Peserta::where('nik', $value)
                                ->where('tahun_id', $tahunId)
                                ->exists();

                            if ($pesertaExists) {
                                $fail('NIK sudah terdaftar untuk tahun ini, gunakan NIK lain atau periksa kembali.');
                            }
                        },
                    ])
                    ->afterStateUpdated(function ($state, $set, $get) {
                        // Memastikan panjang state maksimal 16 digit
                        if (strlen($state) > 16) {
                            $set('nik', substr($state, 0, 16));
                            Notification::make()
                                ->title(__('NIK Tidak Valid'))
                                ->danger()
                                ->body('Pastikan NIK berjumlah 16 digit')
                                ->send();
                        } elseif (strlen($state) < 16) {
                            Notification::make()
                                ->title(__('NIK Tidak Valid'))
                                ->danger()
                                ->body('Pastikan NIK berjumlah 16 digit')
                                ->send();
                        } else {
                            $tahunId = $get('tahun_id');
                            if (!$tahunId) {
                                Notification::make()
                                    ->title(__('Tahun belum dipilih'))
                                    ->danger()
                                    ->send();
                                return;
                            }

                            // Periksa apakah ada peserta dengan NIK dan tahun_id yang sama
                            $pesertaExists = Peserta::where('nik', $state)
                                ->where('tahun_id', $tahunId)
                                ->exists();

                            if ($pesertaExists) {
                                // NIK sudah terdaftar untuk tahun yang sama
                                Notification::make()
                                    ->title(__('NIK Sudah Terdaftar'))
                                    ->danger()
                                    ->body('NIK sudah terdaftar untuk tahun ini, gunakan NIK lain atau periksa kembali.')
                                    ->send();
                            } else {
                                // NIK valid
                                Notification::make()
                                    ->title(__('NIK Valid'))
                                    ->success()
                                    ->send();
                            }
                        }
                    }),
                Forms\Components\TextInput::make('nama')
                    ->label(__('Nama Lengkap'))
                    ->required()
                    ->maxLength(255)
                    ->validationMessages([
                        'required' => 'Kolom Nama tidak boleh kosong',
                    ]),
                Hidden::make('jenis_kelamin')
                    ->label(__('Jenis Kelamin'))
                    ->required(),
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
                    ->displayFormat('d/m/Y')
                    ->live()
                    ->afterStateUpdated(function ($state, $get, $set) {
                        // Lakukan validasi berdasarkan batas umur cabang
                        $cabang = Cabang::find($get('cabang_id'));
                        if ($cabang) {
                            $perTanggal = Carbon::parse($cabang->per_tanggal);
                            $ageInDays = Carbon::parse($state)->diffInDays($perTanggal);

                            // Batas umur diambil dari cabang (misalnya 10 tahun 11 bulan 29 hari)
                            $batasUmur = self::parseAgeString($cabang->batas_umur);
                            $maxAgeDate = $perTanggal->copy()
                                ->subYears($batasUmur['years'])
                                ->subMonths($batasUmur['months'])
                                ->subDays($batasUmur['days']);
                            $maxAgeInDays = $maxAgeDate->diffInDays($perTanggal);

                            if ($ageInDays > $maxAgeInDays + 1) {
                                // Jika melebihi batas umur, kosongkan tanggal lahir
                                $set('tgl_lahir', null);
                                Notification::make()
                                    ->title(__('Usia peserta melebihi batas maksimal untuk cabang ini.'))
                                    ->danger()
                                    ->send();
                            }
                        }
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
                Forms\Components\Textarea::make('alamat_domisili')
                    ->label(__('Alamat Domisili'))
                    ->required()
                    ->maxLength(500)
                    ->validationMessages([
                        'required' => 'Kolom Alamat KTP tidak boleh kosong',
                    ]),
                Hidden::make('utusan_id')
                    ->label(__('Utusan Kecamatan'))
                    ->required()
                    ->default(function () {
                        // Mengatur nilai default ke utusan_id user yang login
                        $user = Auth::user();
                        return $user ? $user->utusan_id : null;
                    })
                    ->live(), // Menambahkan live update),
                FileUpload::make('kk_ktp')
                    ->label(__('Foto KK / KTP'))
                    ->required()
                    ->openable()
                    ->image()
                    ->maxSize(512)
                    ->validationMessages([
                        'required' => 'File KK / KTP belum diunggah',
                    ])
                    ->helperText(new HtmlString('<strong>Petunjuk :</strong> Unggah foto KK/KTP berukuran maksimal 512kb.'))
                    ->moveFiles(),
                FileUpload::make('pasfoto')
                    ->label(__('Pas Foto'))
                    ->required()
                    ->image()
                    ->openable()
                    ->maxSize(512)
                    ->validationMessages([
                        'required' => 'File Pas Foto belum diunggah',
                    ])
                    ->moveFiles()
                    ->imageEditor()
                    ->helperText(new HtmlString('<strong>Petunjuk :</strong> Unggah pas foto berukuran maksimal 512kb.')),
                Hidden::make('user_id')
                    ->default(Auth::id()),
                Hidden::make('tahun_id')
                    ->default(function () {
                        // Mengambil id tahun yang is_active bernilai true
                        $tahun = Tahun::where('is_active', true)->first();
                        return $tahun ? $tahun->id : null;
                    }),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nik')
                    ->label(__('NIK'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama')
                    ->limit(15)
                    ->label(__('Nama'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('tempat_lahir')
                    ->label(__('Tempat Lahir'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('tgl_lahir')
                    ->label(__('Tgl Lahir')),
                Tables\Columns\TextColumn::make('cabang.nama_cabang')
                    ->label(__('Cabang'))
                    ->searchable(),
                Tables\Columns\ImageColumn::make('pasfoto')
                    ->label(__('Pas Foto')),
                Tables\Columns\IconColumn::make('is_verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->label('Status Verifikasi')
                    ->alignment(Alignment::Center),
            ])
            ->emptyStateHeading('Daftar Peserta Kosong')
            ->emptyStateDescription('Silahkan daftarkan peserta dengan memilih "Tambah Peserta"')
            ->paginated(false)
            ->filters([
                SelectFilter::make('cabang.nama_cabang')
                    ->relationship('cabang', 'nama_cabang')
                    ->label(__('Cabang'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label(__('Lihat')),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('utusan_id', Auth::user()->utusan_id);
    }
}
