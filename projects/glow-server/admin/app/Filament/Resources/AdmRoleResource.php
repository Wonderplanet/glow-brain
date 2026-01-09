<?php

namespace App\Filament\Resources;

use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Filament\Resources\RoleResource\Pages;
use App\Models\Adm\AdmRole;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AdmRoleResource extends Resource
{
    use Authorizable;

    protected static ?string $model = AdmRole::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = NavigationGroups::ADMIN->value;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->label('名前')
                    ->hint('ロール名を入力'),
                Forms\Components\Textarea::make('description')->required()->label('説明')
                    ->hint('ロールの説明を入力'),
                Forms\Components\Select::make('permissions')
                    ->multiple()
                    ->relationship('permissions', 'name')
                    ->preload()
                    ->placeholder('')
                    ->label('パーミッション'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID'),
                Tables\Columns\TextColumn::make('name')->label('名前'),
                Tables\Columns\TextColumn::make('description')->label('説明'),
                Tables\Columns\TextColumn::make('permissions.name')->label('付与するパーミッション'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => AdmRoleResource\Pages\ListAdmRoles::route('/'),
            'create' => AdmRoleResource\Pages\CreateAdmRole::route('/create'),
            'edit' => AdmRoleResource\Pages\EditAdmRole::route('/{record}/edit'),
        ];
    }
}
