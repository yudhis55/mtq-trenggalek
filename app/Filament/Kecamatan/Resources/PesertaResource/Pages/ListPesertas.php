<?php

namespace App\Filament\Kecamatan\Resources\PesertaResource\Pages;

use App\Filament\Kecamatan\Resources\PesertaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\ActionSize;
use App\Models\Tahun;
use Carbon\Carbon;

class ListPesertas extends ListRecords
{
    protected static string $resource = PesertaResource::class;

    protected function getHeaderActions(): array
    {
        // Ambil tahun yang aktif dari database
        $tahunAktif = Tahun::where('is_active', true)->first();

        // Cek apakah tahun aktif ditemukan dan apakah tanggal saat ini berada dalam rentang yang diperbolehkan
        if ($tahunAktif && Carbon::now()->between($tahunAktif->batas_awal, $tahunAktif->batas_akhir)) {
            return [
                Actions\CreateAction::make()
                    ->label('Tambah Peserta')
                    ->icon('heroicon-o-user-plus')
                    ->size(ActionSize::Large),
            ];
        }

        // Jika tidak berada dalam rentang, kembalikan array kosong (tombol Create tidak ditampilkan)
        return [];
    }

    protected static ?string $title = 'Daftar Peserta';
}
