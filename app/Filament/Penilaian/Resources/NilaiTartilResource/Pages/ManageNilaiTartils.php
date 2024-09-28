<?php

namespace App\Filament\Penilaian\Resources\NilaiTartilResource\Pages;

use App\Filament\Penilaian\Resources\NilaiTartilResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageNilaiTartils extends ManageRecords
{
    protected static string $resource = NilaiTartilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }


    protected ?string $subheading = 'Pastikan input nilai dengan tepat, karena kesempatan mengisi hanya sekali';
}
