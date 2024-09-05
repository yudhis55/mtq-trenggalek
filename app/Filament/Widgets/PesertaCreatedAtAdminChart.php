<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Peserta;
use Illuminate\Support\Facades\DB;

class PesertaCreatedAtAdminChart extends ChartWidget
{
    protected static ?int $sort = 4;
    protected static ?string $heading = 'Grafik Pendaftar Perhari';

    protected function getData(): array
    {
        $data = Peserta::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        return [
            'labels' => array_keys($data),
            'datasets' => [
                [
                    'label' => 'Jumlah Peserta',
                    'data' => array_values($data),
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
