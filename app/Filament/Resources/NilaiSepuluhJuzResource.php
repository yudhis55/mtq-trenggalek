<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NilaiSepuluhJuzResource\Pages;
use App\Filament\Resources\NilaiSepuluhJuzResource\RelationManagers;
use App\Models\NilaiSepuluhJuz;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NilaiSepuluhJuzResource extends Resource
{
    protected static ?string $model = NilaiSepuluhJuz::class;

    protected static ?int $navigationSort = 107;

    protected static ?string $navigationGroup = 'Penilaian';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ManageNilaiSepuluhJuzs::route('/'),
        ];
    }
}
