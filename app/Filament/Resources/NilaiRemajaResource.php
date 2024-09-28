<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NilaiRemajaResource\Pages;
use App\Filament\Resources\NilaiRemajaResource\RelationManagers;
use App\Models\NilaiRemaja;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NilaiRemajaResource extends Resource
{
    protected static ?string $model = NilaiRemaja::class;

    protected static ?int $navigationSort = 103;

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
            'index' => Pages\ManageNilaiRemajas::route('/'),
        ];
    }
}
