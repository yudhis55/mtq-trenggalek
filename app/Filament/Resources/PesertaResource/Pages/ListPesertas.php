<?php

namespace App\Filament\Resources\PesertaResource\Pages;

use App\Filament\Resources\PesertaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPesertas extends ListRecords
{
    protected static string $resource = PesertaResource::class;

    protected static ?string $title = 'Daftar Peserta';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
