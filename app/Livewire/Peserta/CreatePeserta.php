<?php

namespace App\Livewire\Peserta;

use App\Models\Peserta;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Collection;
use App\Models\Cabang;
use Filament\Forms\Get;
use Carbon\Carbon;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Filament\Actions\Concerns\InteractsWithActions;

class CreatePeserta extends Component implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    public ?array $data = [];

    public array $disabledFields = [
        'nama' => true,
        'jenis_kelamin' => true,
        'tempat_lahir' => true,
        'tgl_lahir' => true,
        'alamat_ktp' => true,
        'utusan_id' => true,
        'cabang_id' => true,
        'kk_ktp' => true,
    ];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nik')
                    ->label(__('NIK'))
                    ->required()
                    ->numeric()
                    ->length(16)
                    ->live()
                    ->unique(column: 'nik')
                    ->afterStateUpdated(function ($state, $set) {
                        $pesertaExists = Peserta::where('nik', $state)->exists();

                        if (!$pesertaExists && strlen($state) == 16) {
                            $this->disabledFields = array_fill_keys(array_keys($this->disabledFields), false);
                        } else {
                            $this->disabledFields = array_fill_keys(array_keys($this->disabledFields), true);
                            $this->disabledFields['nik'] = false; // NIK tetap tidak di-disable
                        }
                    })
                    ->validationMessages([
                        'required' => 'Kolom NIK tidak boleh kosong',
                        'unique' => 'NIK sudah pernah didaftarkan.',
                    ]),

                Forms\Components\TextInput::make('nama')
                    ->label(__('Nama Lengkap'))
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn (Get $get) => $this->disabledFields['nama'])
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
                    ->disabled(fn (Get $get) => $this->disabledFields['jenis_kelamin'])
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
                    ->disabled(fn (Get $get) => $this->disabledFields['tempat_lahir'])
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
                    ->disabled(fn (Get $get) => $this->disabledFields['tgl_lahir'])
                    ->afterStateUpdated(function ($state, $get, $set) {
                        $set('cabang_id', null);
                    })
                    ->validationMessages([
                        'required' => 'Kolom Tanggal Lahir tidak boleh kosong',
                    ]),

                Forms\Components\Textarea::make('alamat_ktp')
                    ->label(__('Alamat KTP'))
                    ->required()
                    ->maxLength(500)
                    ->disabled(fn (Get $get) => $this->disabledFields['alamat_ktp'])
                    ->validationMessages([
                        'required' => 'Kolom Alamat KTP tidak boleh kosong',
                    ]),

                Forms\Components\Select::make('utusan_id')
                    ->label(__('Utusan Kecamatan'))
                    ->required()
                    ->relationship('utusan', 'kecamatan')
                    ->disabled(fn (Get $get) => $this->disabledFields['utusan_id'])
                    ->native(false)
                    ->validationMessages([
                        'required' => 'Kolom Utusan Kecamatan tidak boleh kosong',
                    ]),

                Forms\Components\Select::make('cabang_id')
                    ->required(fn(Get $get): bool => filled($get('tgl_lahir')))
                    ->label(__('Cabang yang Diikuti'))
                    ->required()
                    ->live()
                    ->options(fn(Get $get): Collection => Cabang::query()
                        ->where('gender_cabang', $get('jenis_kelamin'))
                        ->pluck('nama_cabang', 'id'))
                    ->native(false)
                    ->disabled(fn (Get $get) => $this->disabledFields['cabang_id'])
                    ->afterStateUpdated(function ($state, $get, $set) {
                        $birthdate = $get('tgl_lahir');
                        if (!$birthdate) {
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
                    // ->disabled(fn (Get $get) => $this->disabledFields['kk_ktp'])
                    ->validationMessages([
                        'required' => 'File KK / KTP belum diunggah',
                    ])
                    ->helperText(new HtmlString('<strong>Petunjuk :</strong> Unggah foto berukuran maksimal 512kb.'))
                    ->moveFiles(),
            ])
            ->statePath('data')
            ->model(Peserta::class)
            ->columns(1)
            ->extraAttributes(['class' => 'max-w-3xl mx-auto my-10 px-8 py-10 bg-gray-100 rounded-lg shadow-lg']);
    }

    public function create(): void
    {
        $data = $this->form->getState();
        $record = Peserta::create($data);
        $this->form->model($record)->saveRelationships();
    }

    public function render(): View
    {
        return view('livewire.peserta.create-peserta');
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
