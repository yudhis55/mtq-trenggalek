<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PesertaVerifiedResource\Pages;
use App\Filament\Resources\PesertaVerifiedResource\RelationManagers;
use App\Models\PesertaVerified;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Peserta;
use Filament\Tables\Columns\ToggleColumn;
use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;
use Filament\Tables\Columns\IconColumn;

class PesertaVerifiedResource extends Resource
{
    protected static ?string $model = Peserta::class;

    protected static ?string $navigationLabel = 'Diterima';

    protected static ?string $navigationGroup = 'Manajemen Peserta';

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

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
                IconColumn::make('is_verified')
                    ->label('Diterima')
                    ->boolean()
                    ->action(function ($record, $column) {
                        $name = $column->getName();
                        $record->update([
                            $name => !$record->$name
                        ]);
                    }),
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
            'index' => Pages\ListPesertaVerifieds::route('/'),
            'create' => Pages\CreatePesertaVerified::route('/create'),
            'edit' => Pages\EditPesertaVerified::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_verified', 1);
    }
}
