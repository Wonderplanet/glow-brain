<?php

namespace App\Filament\Resources;

use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Filament\Resources\AdmEnvSettingResource\Pages;
use App\Filament\Resources\AdmEnvSettingResource\Pages\ListAdmEnvSettings;
use App\Filament\Resources\AdmEnvSettingResource\Pages\CreateAdmEnvSetting;
use App\Filament\Resources\AdmEnvSettingResource\Pages\ViewAdmEnvSetting;
use App\Filament\Resources\AdmEnvSettingResource\Pages\EditAdmEnvSetting;
use App\Models\Adm\AdmEnvSetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;

class AdmEnvSettingResource extends Resource
{
    use Authorizable;

    protected static ?string $model = AdmEnvSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = NavigationGroups::OTHER->value;

    protected static ?string $navigationLabel = 'バージョン設定';

    protected static ?string $modelLabel = 'バージョン設定';

    protected static ?int $navigationSort = 998;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(static::getFormSchema());
    }

    public static function getFormSchema(): array
    {
        return [
            Section::make('環境設定')
                ->schema([
                    TextInput::make('version')
                        ->label('バージョン')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('client_version_hash')
                        ->label('クライアントバージョンハッシュ')
                        ->required()
                        ->maxLength(255),

                    Textarea::make('env_status')
                        ->label('環境ステータス')
                        ->required()
                        ->rows(3),
                ])
                ->columns(1),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('version')
                    ->label('バージョン')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('client_version_hash')
                    ->label('クライアントバージョンハッシュ')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('env_status_string')
                    ->label('環境ステータス')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) > 50) {
                            return $state;
                        }
                        return null;
                    }),

                TextColumn::make('created_at')
                    ->label('作成日時')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('更新日時')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->actions([
                Action::make('view')
                    ->label('詳細')
                    ->icon('heroicon-o-eye')
                    ->url(fn (AdmEnvSetting $record): string => static::getUrl('view', ['record' => $record])),

                Action::make('edit')
                    ->label('編集')
                    ->icon('heroicon-o-pencil')
                    ->url(fn (AdmEnvSetting $record): string => static::getUrl('edit', ['record' => $record])),
            ], position: ActionsPosition::BeforeColumns);
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
            'index' => ListAdmEnvSettings::route('/'),
            'create' => CreateAdmEnvSetting::route('/create'),
            'view' => ViewAdmEnvSetting::route('/{record}'),
            'edit' => EditAdmEnvSetting::route('/{record}/edit'),
        ];
    }
}
