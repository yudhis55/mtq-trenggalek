<?php

namespace App\Filament\Widgets;

use App\Models\Peserta;
use App\Models\Cabang;
use Filament\Widgets\ChartWidget;

class CabangAdminChart extends ChartWidget
{
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Sebaran Peserta tiap Cabang Lomba';

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $data = Peserta::selectRaw('cabang_id, COUNT(*) as total')
            ->groupBy('cabang_id')
            ->get()
            ->mapWithKeys(function ($item) {
                $cabang = Cabang::find($item->cabang_id);
                return [$cabang ? $cabang->nama_cabang : 'Unknown' => $item->total];
            });

        return [
            'labels' => $data->keys()->toArray(),
            'datasets' => [
                [
                    'data' => $data->values()->toArray(),
                    'backgroundColor' => [
                        '#704627',
                        '#0126bd',
                        '#7fb200',
                        '#00bf4f',
                        '#ef3df0',
                        '#7f62ff',
                        '#bb9300',
                        '#296300',
                        '#497bff',
                        '#352796',
                        '#a9007f',
                        '#d40079',
                        '#79ba7d',
                        '#cc5100',
                        '#00bda5',
                        '#0e4518',
                        '#4293ff',
                        '#ff4d7e',
                        '#ef9650',
                        '#0050a9',
                        '#651b61',
                        '#e48ce2',
                        '#9c3200',
                        '#884200',
                        '#ff7153',
                        '#721637',
                        '#0178c0',
                        '#1cbcd0',
                        '#c4976f',
                        '#711d13',
                        '#e992a9',
                        '#5f78aa',
                        // Tambahkan warna lain sesuai jumlah cabang
                    ],
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => false, // Menyembunyikan sumbu X
                ],
                'y' => [
                    'display' => false, // Menyembunyikan sumbu Y
                ],
            ],
        ];
    }
}
