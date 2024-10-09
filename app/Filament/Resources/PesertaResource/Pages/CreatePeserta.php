<?php

namespace App\Filament\Resources\PesertaResource\Pages;

use App\Models\Grup;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\PesertaResource;

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

    protected function afterCreate(): void
    {
        $cabangId = $this->record->cabang_id;
        $tahunId = $this->record->tahun_id;
        $utusanId = $this->record->utusan_id;
        $jeniskelamin = $this->record->jenis_kelamin;

        if($cabangId == 19 || $cabangId == 20){
            $grup = Grup::where('tahun_id', $tahunId)->where('utusan_id', $utusanId)->where('jenis_kelamin', $jeniskelamin)->first();
            $this->record->grup_id = $grup->id;
            $this->record->save();
        }
    }

}
