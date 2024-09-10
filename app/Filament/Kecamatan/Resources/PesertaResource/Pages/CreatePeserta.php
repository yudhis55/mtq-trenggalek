<?php

namespace App\Filament\Kecamatan\Resources\PesertaResource\Pages;

use App\Filament\Kecamatan\Resources\PesertaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePeserta extends CreateRecord
{
    protected static string $resource = PesertaResource::class;

    protected static ?string $title = 'Tambah Peserta';

}
