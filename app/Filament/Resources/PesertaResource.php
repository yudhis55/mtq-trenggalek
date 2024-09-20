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
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
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
use App\Models\Tahun;
use Illuminate\Support\Str;
use Filament\Notifications\Livewire\Notifications;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
use Closure;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use Filament\Tables\Columns\IconColumn;


class PesertaResource extends Resource
{
    protected static ?string $model = Peserta::class;

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
                Tables\Columns\TextColumn::make('id')
                    ->label(__('No'))
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
                    ->toggleable(),
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
                        $record->update([
                            $name => !$record->$name
                        ]);
                    })
                    ->toggleable(),
                // Tables\Columns\TextColumn::make('tahun.tahun')
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
            ->columns(2);
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
