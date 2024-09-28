<?php

namespace App\Filament\Resources;

use Closure;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Tahun;
use App\Models\Cabang;
use App\Models\Peserta;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use PhpParser\Node\Stmt\Label;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Actions\CreateAction;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Support\Enums\VerticalAlignment;
use Illuminate\Validation\ValidationException;
use App\Filament\Resources\PesertaResource\Pages;
use Filament\Infolists\Components\Actions\Action;
use Filament\Notifications\Livewire\Notifications;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use App\Filament\Resources\PesertaResource\RelationManagers;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;

class PesertaResource extends Resource
{
    protected static ?string $model = Peserta::class;

    protected static ?int $navigationSort = 51;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Peserta';

    protected static ?string $navigationGroup = 'Manajemen Peserta';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                                $fail('NIK sudah pernah didaftarkan, periksa kembali');
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
                                    ->body('NIK sudah pernah didaftarkan, periksa kembali')
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
                    ->displayFormat('d-m-Y')
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
                Forms\Components\Textarea::make('alamat_domisili')
                    ->label(__('Alamat Domisili'))
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
                    ->live() // Menambahkan live update
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $set('cabang_id', null); // Kosongkan field cabang_id saat utusan_id berubah
                    })
                    ->validationMessages([
                        'required' => 'Kolom Utusan Kecamatan tidak boleh kosong',
                    ]),
                Forms\Components\Select::make('cabang_id')
                    ->label(__('Cabang yang Diikuti'))
                    ->preload()
                    ->relationship('cabang', 'nama_cabang')
                    ->required()
                    ->live()
                    ->options(function (Get $get) {
                        $utusanId = $get('utusan_id');
                        $jenisKelamin = $get('jenis_kelamin');
                        $tahunId = $get('tahun_id');

                        if (!$utusanId || !$jenisKelamin || !$tahunId) {
                            return [];
                        }

                        return Cabang::query()
                            ->where('gender_cabang', $jenisKelamin)
                            ->get()
                            ->filter(function ($cabang) use ($utusanId, $tahunId) {
                                $kuota = $cabang->kuota;

                                $jumlahPeserta = Peserta::where('utusan_id', $utusanId)
                                    ->where('tahun_id', $tahunId)
                                    ->where('cabang_id', $cabang->id)
                                    ->count();

                                return $jumlahPeserta < $kuota;
                            })
                            ->pluck('nama_cabang', 'id');
                    })
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

                            if ($ageInDays > $maxAgeInDays + 1) {
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
                TextColumn::make('index')
                    ->label('No')
                    ->rowIndex()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('nik')
                    ->label(__('NIK'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('nama')
                    ->limit(15)
                    ->label(__('Nama'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->label(__('L/P'))
                    ->toggleable()
                    ->formatStateUsing(fn (Peserta $record): string => $record->jenis_kelamin == 'putra' ? 'L' : 'P'),
                Tables\Columns\TextColumn::make('tempat_lahir')
                    ->label(__('Tempat Lahir'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tgl_lahir')
                    ->label(__('Tgl Lahir'))
                    ->date('d-m-Y')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('alamat_ktp')
                    ->limit(20)
                    ->label(__('Alamat KTP'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('utusan.kecamatan')
                    ->label(__('Utusan'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('cabang.nama_cabang')
                    ->label(__('Cabang'))
                    ->searchable()
                    ->toggleable(),
                IconColumn::make('is_verified')
                    ->label(__('Verifikasi'))
                    ->size(IconColumn\IconColumnSize::ExtraLarge)
                    ->boolean()
                    ->action(function ($record, $column) {
                        $name = $column->getName();
                        $isVerified = !$record->$name;

                        // Jika verifikasi berhasil
                        if ($isVerified) {
                            if (in_array($record->cabang_id, [1, 2])) {
                                // Tambahkan data baru di tabel nilai_tartils
                                \App\Models\NilaiTartil::create([
                                    'peserta_id' => $record->id,
                                ]);
                                Notification::make()
                                    ->title('Verifikasi Berhasil')
                                    ->body('Peserta ' . $record->nama . ' berhasil diverifikasi')
                                    ->success()
                                    ->send();
                            }
                            if (in_array($record->cabang_id, [3, 4])) {
                                // Tambahkan data baru di tabel nilai_tartils
                                \App\Models\NilaiAnak::create([
                                    'peserta_id' => $record->id,
                                ]);
                                Notification::make()
                                    ->title('Verifikasi Berhasil')
                                    ->body('Peserta ' . $record->nama . ' berhasil diverifikasi')
                                    ->success()
                                    ->send();
                            }
                            if (in_array($record->cabang_id, [5, 6])) {
                                // Tambahkan data baru di tabel nilai_tartils
                                \App\Models\NilaiRemaja::create([
                                    'peserta_id' => $record->id,
                                ]);
                                Notification::make()
                                    ->title('Verifikasi Berhasil')
                                    ->body('Peserta ' . $record->nama . ' berhasil diverifikasi')
                                    ->success()
                                    ->send();
                            }
                            if (in_array($record->cabang_id, [7, 8])) {
                                // Tambahkan data baru di tabel nilai_tartils
                                \App\Models\NilaiDewasa::create([
                                    'peserta_id' => $record->id,
                                ]);
                                Notification::make()
                                    ->title('Verifikasi Berhasil')
                                    ->body('Peserta ' . $record->nama . ' berhasil diverifikasi')
                                    ->success()
                                    ->send();
                            }
                            if (in_array($record->cabang_id, [9, 10])) {
                                // Tambahkan data baru di tabel nilai_tartils
                                \App\Models\NilaiSatuJuz::create([
                                    'peserta_id' => $record->id,
                                ]);
                                Notification::make()
                                    ->title('Verifikasi Berhasil')
                                    ->body('Peserta ' . $record->nama . ' berhasil diverifikasi')
                                    ->success()
                                    ->send();
                            }
                            if (in_array($record->cabang_id, [11, 12])) {
                                // Tambahkan data baru di tabel nilai_tartils
                                \App\Models\NilaiLimaJuz::create([
                                    'peserta_id' => $record->id,
                                ]);
                                Notification::make()
                                    ->title('Verifikasi Berhasil')
                                    ->body('Peserta ' . $record->nama . ' berhasil diverifikasi')
                                    ->success()
                                    ->send();
                            }
                            if (in_array($record->cabang_id, [13, 14])) {
                                // Tambahkan data baru di tabel nilai_tartils
                                \App\Models\NilaiSepuluhJuz::create([
                                    'peserta_id' => $record->id,
                                ]);
                                Notification::make()
                                    ->title('Verifikasi Berhasil')
                                    ->body('Peserta ' . $record->nama . ' berhasil diverifikasi')
                                    ->success()
                                    ->send();
                            }
                            if (in_array($record->cabang_id, [15, 16])) {
                                // Tambahkan data baru di tabel nilai_tartils
                                \App\Models\NilaiDuapuluhJuz::create([
                                    'peserta_id' => $record->id,
                                ]);
                                Notification::make()
                                    ->title('Verifikasi Berhasil')
                                    ->body('Peserta ' . $record->nama . ' berhasil diverifikasi')
                                    ->success()
                                    ->send();
                            }
                            if (in_array($record->cabang_id, [17, 18])) {
                                // Tambahkan data baru di tabel nilai_tartils
                                \App\Models\NilaiTigapuluhJuz::create([
                                    'peserta_id' => $record->id,
                                ]);
                                Notification::make()
                                    ->title('Verifikasi Berhasil')
                                    ->body('Peserta ' . $record->nama . ' berhasil diverifikasi')
                                    ->success()
                                    ->send();
                            }
                            if (in_array($record->cabang_id, [19, 20])) {
                                // Tambahkan data baru di tabel nilai_tartils
                                \App\Models\NilaiMfq::create([
                                    'peserta_id' => $record->id,
                                ]);
                                Notification::make()
                                    ->title('Verifikasi Berhasil')
                                    ->body('Peserta ' . $record->nama . ' berhasil diverifikasi')
                                    ->success()
                                    ->send();
                            }
                            if (in_array($record->cabang_id, [21, 22])) {
                                // Tambahkan data baru di tabel nilai_tartils
                                \App\Models\NilaiMsq::create([
                                    'peserta_id' => $record->id,
                                ]);
                                Notification::make()
                                    ->title('Verifikasi Berhasil')
                                    ->body('Peserta ' . $record->nama . ' berhasil diverifikasi')
                                    ->success()
                                    ->send();
                            }
                            if (in_array($record->cabang_id, [23, 24])) {
                                // Tambahkan data baru di tabel nilai_tartils
                                \App\Models\NilaiNaskah::create([
                                    'peserta_id' => $record->id,
                                ]);
                                Notification::make()
                                    ->title('Verifikasi Berhasil')
                                    ->body('Peserta ' . $record->nama . ' berhasil diverifikasi')
                                    ->success()
                                    ->send();
                            }
                            if (in_array($record->cabang_id, [25, 26])) {
                                // Tambahkan data baru di tabel nilai_tartils
                                \App\Models\NilaiMushaf::create([
                                    'peserta_id' => $record->id,
                                ]);
                                Notification::make()
                                    ->title('Verifikasi Berhasil')
                                    ->body('Peserta ' . $record->nama . ' berhasil diverifikasi')
                                    ->success()
                                    ->send();
                            }
                            if (in_array($record->cabang_id, [27, 28])) {
                                // Tambahkan data baru di tabel nilai_tartils
                                \App\Models\NilaiDekorasi::create([
                                    'peserta_id' => $record->id,
                                ]);
                                Notification::make()
                                    ->title('Verifikasi Berhasil')
                                    ->body('Peserta ' . $record->nama . ' berhasil diverifikasi')
                                    ->success()
                                    ->send();
                            }
                            if (in_array($record->cabang_id, [29, 30])) {
                                // Tambahkan data baru di tabel nilai_tartils
                                \App\Models\NilaiKontemporer::create([
                                    'peserta_id' => $record->id,
                                ]);
                                Notification::make()
                                    ->title('Verifikasi Berhasil')
                                    ->body('Peserta ' . $record->nama . ' berhasil diverifikasi')
                                    ->success()
                                    ->send();
                            }
                            if (in_array($record->cabang_id, [31, 32])) {
                                // Tambahkan data baru di tabel nilai_tartils
                                \App\Models\NilaiMmq::create([
                                    'peserta_id' => $record->id,
                                ]);
                                Notification::make()
                                    ->title('Verifikasi Berhasil')
                                    ->body('Peserta ' . $record->nama . ' berhasil diverifikasi')
                                    ->success()
                                    ->send();
                            }
                        } else {
                            // Jika membatalkan verifikasi, pastikan total null atau 0
                            $nilaiTartil = \App\Models\NilaiTartil::where('peserta_id', $record->id)->first();
                            $nilaiAnak = \App\Models\NilaiAnak::where('peserta_id', $record->id)->first();
                            $nilaiRemaja = \App\Models\NilaiRemaja::where('peserta_id', $record->id)->first();
                            $nilaiDewasa = \App\Models\NilaiDewasa::where('peserta_id', $record->id)->first();
                            $nilaiSatuJuz = \App\Models\NilaiSatuJuz::where('peserta_id', $record->id)->first();
                            $nilaiLimaJuz = \App\Models\NilaiLimaJuz::where('peserta_id', $record->id)->first();
                            $nilaiSepuluhJuz = \App\Models\NilaiSepuluhJuz::where('peserta_id', $record->id)->first();
                            $nilaiDuapuluhJuz = \App\Models\NilaiDuapuluhJuz::where('peserta_id', $record->id)->first();
                            $nilaiTigapuluhJuz = \App\Models\NilaiTigapuluhJuz::where('peserta_id', $record->id)->first();
                            $nilaiMfq = \App\Models\NilaiMfq::where('peserta_id', $record->id)->first();
                            $nilaiMsq = \App\Models\NilaiMsq::where('peserta_id', $record->id)->first();
                            $nilaiNaskah = \App\Models\NilaiNaskah::where('peserta_id', $record->id)->first();
                            $nilaiMushaf = \App\Models\NilaiMushaf::where('peserta_id', $record->id)->first();
                            $nilaiDekorasi = \App\Models\NilaiDekorasi::where('peserta_id', $record->id)->first();
                            $nilaiKontemporer = \App\Models\NilaiKontemporer::where('peserta_id', $record->id)->first();
                            $nilaiMmq = \App\Models\NilaiMmq::where('peserta_id', $record->id)->first();

                            if ($nilaiTartil && ($nilaiTartil->total === null || $nilaiTartil->total == 0)) {
                                // Hapus data jika memenuhi syarat
                                $nilaiTartil->delete();
                            } elseif ($nilaiAnak && ($nilaiAnak->total === null || $nilaiAnak->total == 0)) {
                                // Hapus data jika memenuhi syarat
                                $nilaiAnak->delete();
                            } elseif ($nilaiRemaja && ($nilaiRemaja->total === null || $nilaiRemaja->total == 0)) {
                                // Hapus data jika memenuhi syarat
                                $nilaiRemaja->delete();
                            } elseif ($nilaiDewasa && ($nilaiDewasa->total === null || $nilaiDewasa->total == 0)) {
                                // Hapus data jika memenuhi syarat
                                $nilaiDewasa->delete();
                            } elseif ($nilaiSatuJuz && ($nilaiSatuJuz->total === null || $nilaiSatuJuz->total == 0)) {
                                // Hapus data jika memenuhi syarat
                                $nilaiSatuJuz->delete();
                            } elseif ($nilaiLimaJuz && ($nilaiLimaJuz->total === null || $nilaiLimaJuz->total == 0)) {
                                // Hapus data jika memenuhi syarat
                                $nilaiLimaJuz->delete();
                            } elseif ($nilaiSepuluhJuz && ($nilaiSepuluhJuz->total === null || $nilaiSepuluhJuz->total == 0)) {
                                // Hapus data jika memenuhi syarat
                                $nilaiSepuluhJuz->delete();
                            } elseif ($nilaiDuapuluhJuz && ($nilaiDuapuluhJuz->total === null || $nilaiDuapuluhJuz->total == 0)) {
                                // Hapus data jika memenuhi syarat
                                $nilaiDuapuluhJuz->delete();
                            } elseif ($nilaiTigapuluhJuz && ($nilaiTigapuluhJuz->total === null || $nilaiTigapuluhJuz->total == 0)) {
                                // Hapus data jika memenuhi syarat
                                $nilaiTigapuluhJuz->delete();
                            } elseif ($nilaiMfq && ($nilaiMfq->total === null || $nilaiMfq->total == 0)) {
                                // Hapus data jika memenuhi syarat
                                $nilaiMfq->delete();
                            } elseif ($nilaiMsq && ($nilaiMsq->total === null || $nilaiMsq->total == 0)) {
                                // Hapus data jika memenuhi syarat
                                $nilaiMsq->delete();
                            } elseif ($nilaiNaskah && ($nilaiNaskah->total === null || $nilaiNaskah->total == 0)) {
                                // Hapus data jika memenuhi syarat
                                $nilaiNaskah->delete();
                            } elseif ($nilaiMushaf && ($nilaiMushaf->total === null || $nilaiMushaf->total == 0)) {
                                // Hapus data jika memenuhi syarat
                                $nilaiMushaf->delete();
                            } elseif ($nilaiDekorasi && ($nilaiDekorasi->total === null || $nilaiDekorasi->total == 0)) {
                                // Hapus data jika memenuhi syarat
                                $nilaiDekorasi->delete();
                            } elseif ($nilaiKontemporer && ($nilaiKontemporer->total === null || $nilaiKontemporer->total == 0)) {
                                // Hapus data jika memenuhi syarat
                                $nilaiKontemporer->delete();
                            } elseif ($nilaiMmq && ($nilaiMmq->total === null || $nilaiMmq->total == 0)) {
                                // Hapus data jika memenuhi syarat
                                $nilaiMmq->delete();
                            } else {
                                // Mungkin tambahkan notifikasi bahwa penghapusan gagal
                                // return back()->with('error', 'Gagal membatalkan verifikasi: total tidak null atau tidak 0.');
                                return Notification::make()
                                    ->title('Pembatalan Verifikasi Gagal')
                                    ->body('Silahkan kosongkan terlebih dahulu nilai dari peserta ' . $record->nama)
                                    ->danger()
                                    ->send();
                            }
                            Notification::make()
                                ->title('Pembatalan Verifikasi Berhasil')
                                ->body('Pembatalan verifikasi peserta ' . $record->nama . ' berhasil dilakukan')
                                ->success()
                                ->send();
                        }

                        // Update status verifikasi
                        $record->update([$name => $isVerified]);
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tahun.tahun')
            ])
            ->filters([
                SelectFilter::make('utusan.kecamatan')
                    ->relationship('utusan', 'kecamatan')
                    ->label(__('Kecamatan'))
                    ->native(false),
                SelectFilter::make('cabang_merged')
                    ->label('Cabang')
                    ->options([
                        'tartil' => 'Tartil',
                        'tilawah anak-anak' => 'Tilawah Anak-anak',
                        'tilawah remaja' => 'Tilawah Remaja',
                        'tilawah dewasa' => 'Tilawah Dewasa',
                        'mhq 1 juz dan tilawah' => 'MHQ 1 juz dan Tilawah',
                        'mhq 5 juz dan tilawah' => 'MHQ 5 juz dan Tilawah',
                        'mhq 10 juz' => 'MHQ 10 juz',
                        'mhq 20 juz' => 'MHQ 20 juz',
                        'mhq 30 juz' => 'MHQ 30 juz',
                        'mfq' => 'MFQ',
                        'msq' => 'MSQ',
                        'mkq naskah' => 'MKQ Naskah',
                        'mkq hiasan mushaf' => 'MKQ Hiasan Mushaf',
                        'mkq dekorasi' => 'MKQ Dekorasi',
                        'mkq kontemporer' => 'MKQ Kontemporer',
                        'mmq' => 'MMQ',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? null;

                        if ($value === 'tartil') {
                            // Filter peserta dengan cabang Tartil Putra atau Tartil Putri
                            return $query->whereHas('cabang', function (Builder $query) {
                                $query->where('nama_cabang', 'like', '%Tartil%');
                            });
                        } elseif ($value === 'tilawah anak-anak') {
                            // Filter peserta dengan cabang Tilawah Anak-anak Putra atau Tilawah Anak-anak Putri
                            return $query->whereHas('cabang', function (Builder $query) {
                                $query->where('nama_cabang', 'like', '%Tilawah Anak-anak%');
                            });
                        } elseif ($value === 'tilawah remaja') {
                            // Filter peserta dengan cabang Tilawah Remaja Putra atau Tilawah Remaja Putri
                            return $query->whereHas('cabang', function (Builder $query) {
                                $query->where('nama_cabang', 'like', '%Tilawah Remaja%');
                            });
                        } elseif ($value === 'tilawah dewasa') {
                            // Filter peserta dengan cabang Tilawah Dewasa Putra atau Tilawah Dewasa Putri
                            return $query->whereHas('cabang', function (Builder $query) {
                                $query->where('nama_cabang', 'like', '%Tilawah Dewasa%');
                            });
                        } elseif ($value === 'mhq 1 juz dan tilawah') {
                            // Filter peserta dengan cabang MHQ 1 juz dan Tilawah Putra atau MHQ 1 juz dan Tilawah Putri
                            return $query->whereHas('cabang', function (Builder $query) {
                                $query->where('nama_cabang', 'like', '%MHQ 1 juz dan Tilawah%');
                            });
                        } elseif ($value === 'mhq 5 juz dan tilawah') {
                            // Filter peserta dengan cabang MHQ 5 juz dan Tilawah Putra atau MHQ 5 juz dan Tilawah Putri
                            return $query->whereHas('cabang', function (Builder $query) {
                                $query->where('nama_cabang', 'like', '%MHQ 5 juz dan Tilawah%');
                            });
                        } elseif ($value === 'mhq 10 juz') {
                            // Filter peserta dengan cabang MHQ 10 juz Putra atau MHQ 10 juz Putri
                            return $query->whereHas('cabang', function (Builder $query) {
                                $query->where('nama_cabang', 'like', '%MHQ 10 juz%');
                            });
                        } elseif ($value === 'mhq 20 juz') {
                            // Filter peserta dengan cabang MHQ 20 juz Putra atau MHQ 20 juz Putri
                            return $query->whereHas('cabang', function (Builder $query) {
                                $query->where('nama_cabang', 'like', '%MHQ 20 juz%');
                            });
                        } elseif ($value === 'mhq 30 juz') {
                            // Filter peserta dengan cabang MHQ 30 juz Putra atau MHQ 30 juz Putri
                            return $query->whereHas('cabang', function (Builder $query) {
                                $query->where('nama_cabang', 'like', '%MHQ 30 juz%');
                            });
                        } elseif ($value === 'mfq') {
                            // Filter peserta dengan cabang MFQ Putra atau MFQ Putri
                            return $query->whereHas('cabang', function (Builder $query) {
                                $query->where('nama_cabang', 'like', '%MFQ%');
                            });
                        } elseif ($value === 'msq') {
                            // Filter peserta dengan cabang MSQ Putra atau MSQ Putri
                            return $query->whereHas('cabang', function (Builder $query) {
                                $query->where('nama_cabang', 'like', '%MSQ%');
                            });
                        } elseif ($value === 'mkq naskah') {
                            // Filter peserta dengan cabang MKQ Naskah Putra atau MKQ Naskah Putri
                            return $query->whereHas('cabang', function (Builder $query) {
                                $query->where('nama_cabang', 'like', '%MKQ Naskah%');
                            });
                        } elseif ($value === 'mkq hiasan mushaf') {
                            // Filter peserta dengan cabang MKQ Hiasan Mushaf Putra atau MKQ Hiasan Mushaf Putri
                            return $query->whereHas('cabang', function (Builder $query) {
                                $query->where('nama_cabang', 'like', '%MKQ Hiasan Mushaf%');
                            });
                        } elseif ($value === 'mkq dekorasi') {
                            // Filter peserta dengan cabang MKQ Dekorasi Putra atau MKQ Dekorasi Putri
                            return $query->whereHas('cabang', function (Builder $query) {
                                $query->where('nama_cabang', 'like', '%MKQ Dekorasi%');
                            });
                        } elseif ($value === 'mkq kontemporer') {
                            // Filter peserta dengan cabang MKQ Kontemporer Putra atau MKQ Kontemporer Putri
                            return $query->whereHas('cabang', function (Builder $query) {
                                $query->where('nama_cabang', 'like', '%MKQ Kontemporer%');
                            });
                        } elseif ($value === 'mmq') {
                            // Filter peserta dengan cabang MMQ Putra atau MMQ Putri
                            return $query->whereHas('cabang', function (Builder $query) {
                                $query->where('nama_cabang', 'like', '%MMQ%');
                            });
                        }
                        return $query;
                    })
                    ->native(false),
                SelectFilter::make('cabang.nama_cabang')
                    ->relationship('cabang', 'nama_cabang')
                    ->label(__('Kategori'))
                    ->native(false),
            ])
            ->headerActions([
                ExportAction::make('export')
                    ->label(__('Download Excel'))
                    ->color('success'),
                Tables\Actions\CreateAction::make()
                    ->label(__('Tambah Peserta'))
                    ->icon('heroicon-o-user-plus'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label(__('Lihat')),
                Tables\Actions\EditAction::make()
                    ->label(__('Edit')),
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('nama')
                    ->label(__('Nama')),
                TextEntry::make('nik')
                    ->label(__('NIK')),
                ImageEntry::make('pasfoto'),
                TextEntry::make('tempat_lahir'),
                TextEntry::make('tgl_lahir'),
                TextEntry::make('alamat_ktp'),
                // TextEntry::make('alamat_domisili'),
                ImageEntry::make('kk_ktp')
                    ->columnSpanFull()
                    ->label(__('KTP / KK'))
                    ->width(800)
                    ->height(600),

            ])
            ->columns(3);
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
