<?php

namespace App\Filament\Resources;

use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Filament\Resources\UsrUserResource\Pages;
use App\Models\Usr\UsrUser as UsrUser;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UsrUserResource extends Resource
{
    use Authorizable;

    protected static ?string $model = UsrUser::class;

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = NavigationGroups::USER->value;
    protected static ?string $navigationLabel = 'ユーザー';

    protected static ?string $breadcrumb = 'ユーザー';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->label('名前'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Id')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('名前')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('作成日')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('更新日')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('Id'),
                        Infolists\Components\TextEntry::make('name')
                            ->label('名前'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('作成日')
                            ->date(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('更新日')
                            ->date(),
                    ])
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
            'index' => Pages\ListUsrUsers::route('/'),
            'view' => Pages\ViewUsrUser::route('{record}'),
        ];
    }
}
