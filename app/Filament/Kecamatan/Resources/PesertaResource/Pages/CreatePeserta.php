<?php

namespace App\Filament\Kecamatan\Resources\PesertaResource\Pages;

use App\Filament\Kecamatan\Resources\PesertaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePeserta extends CreateRecord
{
    protected static string $resource = PesertaResource::class;

    protected static ?string $title = 'Tambah Peserta';

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil')
            ->body('Peserta berhasil didaftarkan');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
