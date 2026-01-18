<?php

namespace App\Filament\Resources;

use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Filament\Resources\UserResource\Pages;
use App\Models\Adm\AdmUser;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class AdmUserResource extends Resource
{
    use Authorizable;

    protected static ?string $model = AdmUser::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = NavigationGroups::ADMIN->value;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->label('名前'),

                Forms\Components\TextInput::make('email')->required()->label('メールアドレス')
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->same('password_confirmation')
                    ->label('パスワード'),

                Forms\Components\TextInput::make('password_confirmation')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrated(false)
                    ->label('パスワード確認'),

                Forms\Components\TextInput::make('slack_id')
                    ->label('Slack ID'),

                Forms\Components\Select::make('roles')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->preload()
                    ->placeholder('')
                    ->label('役割'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('名前'),
                Tables\Columns\TextColumn::make('email')->label('メールアドレス'),
                Tables\Columns\TextColumn::make('roles.name')->label('役割'),
                Tables\Columns\TextColumn::make('slack_id')->label('Slack ID')
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
            'index' => AdmUserResource\Pages\ListAdmUsers::route('/'),
            'create' => AdmUserResource\Pages\CreateAdmUser::route('/create'),
            'edit' => AdmUserResource\Pages\EditAdmUser::route('/{record}/edit'),
        ];
    }
}
