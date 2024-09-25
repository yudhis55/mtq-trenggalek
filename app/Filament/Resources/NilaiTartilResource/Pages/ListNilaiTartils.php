<?php

namespace App\Filament\Resources\NilaiTartilResource\Pages;

use App\Filament\Resources\NilaiTartilResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNilaiTartils extends ListRecords
{
    protected static string $resource = NilaiTartilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
