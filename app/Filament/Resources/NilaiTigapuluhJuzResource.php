<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NilaiTigapuluhJuzResource\Pages;
use App\Filament\Resources\NilaiTigapuluhJuzResource\RelationManagers;
use App\Models\NilaiTigapuluhJuz;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NilaiTigapuluhJuzResource extends Resource
{
    protected static ?string $model = NilaiTigapuluhJuz::class;

    protected static ?int $navigationSort = 109;

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
            'index' => Pages\ManageNilaiTigapuluhJuzs::route('/'),
        ];
    }
}
