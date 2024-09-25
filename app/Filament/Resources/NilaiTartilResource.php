<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\NilaiTartil;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\NilaiTartilResource\Pages;
use App\Filament\Resources\NilaiTartilResource\RelationManagers;

class NilaiTartilResource extends Resource
{
    protected static ?string $model = NilaiTartil::class;

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationGroup = 'Penilaian';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('peserta_id')
                    ->relationship('peserta', 'nama'),
                TextInput::make('tajwid')
                    ->numeric(),
                TextInput::make('irama_dan_suara')
                    ->numeric(),
                TextInput::make('fashahah')
                    ->numeric(),
                TextInput::make('total')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('peserta.nama')
                    ->label('Nama'),
                TextColumn::make('peserta.jenis_kelamin')
                    ->label('Jenis Kelamin'),
                TextColumn::make('peserta.utusan.kecamatan'),
                TextColumn::make('tajwid'),
                TextColumn::make('irama_dan_suara'),
                TextColumn::make('fashahah'),
                TextColumn::make('total'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Input Nilai'),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageNilaiTartils::route('/'),
        ];
    }
}
