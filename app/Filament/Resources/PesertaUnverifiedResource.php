<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PesertaUnverifiedResource\Pages;
use App\Filament\Resources\PesertaUnverifiedResource\RelationManagers;
use App\Models\PesertaUnverified;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Peserta;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;

class PesertaUnverifiedResource extends Resource
{
    protected static ?string $model = Peserta::class;

    protected static ?string $navigationLabel = 'Total Pendaftar';

    protected static ?string $navigationIcon = 'heroicon-o-x-circle';

    protected static ?string $navigationGroup = 'Manajemen Peserta';

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
                TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('nik'),
                TextColumn::make('nama'),
                TextColumn::make('alamat_ktp')
                    ->wrap(),
                ToggleColumn::make('is_verified')
                    ->label('Diterima'),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            'index' => Pages\ListPesertaUnverifieds::route('/'),
            // 'create' => Pages\CreatePesertaUnverified::route('/create'),
            // 'edit' => Pages\EditPesertaUnverified::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_verified', 0);
    }
}
