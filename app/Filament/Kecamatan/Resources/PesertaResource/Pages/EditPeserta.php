<?php

namespace App\Filament\Kecamatan\Resources\PesertaResource\Pages;

use App\Filament\Kecamatan\Resources\PesertaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\ActionSize;

class EditPeserta extends EditRecord
{
    protected static string $resource = PesertaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus Peserta')
                ->size(ActionSize::Large)
                ->icon('heroicon-o-user-minus'),
        ];
    }

    protected static ?string $title = 'Edit Peserta';
}
