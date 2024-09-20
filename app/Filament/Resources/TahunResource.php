<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TahunResource\Pages;
use App\Filament\Resources\TahunResource\RelationManagers;
use App\Models\Tahun;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\ToggleColumn;

class TahunResource extends Resource
{
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationLabel = 'Tahun';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $model = Tahun::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-date-range';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('tahun')
                    ->label('Tahun')
                    ->required()
                    ->numeric()
                    ->unique(ignoreRecord: true),
                DatePicker::make('batas_awal')
                    ->label('Tanggal Dibuka')
                    ->required()
                    ->native(false)
                    ->closeOnDateSelection()
                    ->displayFormat('d-m-Y'),
                DatePicker::make('batas_akhir')
                    ->label('Tanggal Ditutup')
                    ->required()
                    ->native(false)
                    ->closeOnDateSelection()
                    ->displayFormat('d-m-Y'),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tahun'),
                ToggleColumn::make('is_active')
                    ->label('Status Aktif'),
                TextColumn::make('batas_awal')
                    ->label('Tanggal Dibuka')
                    ->date('d-m-Y'),
                TextColumn::make('batas_akhir')
                    ->label('Tanggal Ditutup')
                    ->date('d-m-Y'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTahuns::route('/'),
            'create' => Pages\CreateTahun::route('/create'),
            'edit' => Pages\EditTahun::route('/{record}/edit'),
        ];
    }
}
