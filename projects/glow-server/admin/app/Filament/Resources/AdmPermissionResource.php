<?php

namespace App\Filament\Resources;

use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Filament\Resources\PermissionResource\Pages;
use App\Models\Adm\AdmPermission;
use App\Models\Adm\AdmPermissionFeature;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AdmPermissionResource extends Resource
{
    use Authorizable;

    protected static ?string $model = AdmPermission::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = NavigationGroups::ADMIN->value;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->label('名前')
                    ->helperText('パーミッション名を入力します。具体的にどの機能を許可するかはエンジニア側で設定する必要があります。'),
                Forms\Components\Textarea::make('description')->label('説明')
                    ->helperText('どの機能を許可するかの説明'),
                Section::make('ページ権限')
                    ->description('このパーミッションで閲覧を許可するページを管理します。')
                    ->visible(fn ($record) => $record->name !== 'AdministratorAccess')
                    ->collapsed()
                    ->schema(function ($record) {
                        $features = AdmPermissionFeature::query()
                            ->where('permission_id', $record->id)
                            ->get()
                            ->pluck('feature_name')
                            ->toArray();
                        $pages = Filament::getPages();
                        $options = [];
                        $default = [];
                        foreach ($pages as $page) {
                            $tmp = class_basename($page);
                            $group = $page::getNavigationGroup() ?? 'その他';
                            $options[$group][$tmp] = $tmp;
                            if (in_array($tmp, $features)) {
                                $default[$group][] = $tmp;
                            }
                        }
                        $result = [];
                        foreach ($options as $group => $items) {
                            $result[] = Section::make()
                                ->schema([
                                    CheckboxList::make($group)
                                        ->label($group)
                                        ->options($items)
                                        ->columns(4)
                                        ->formatStateUsing(fn() => $default[$group] ?? [])
                                ])
                                ->label($group);
                        }
                        return $result;
                    }),
                Section::make('リソース権限')
                    ->description('このパーミッションで閲覧を許可するリソースを管理します。')
                    ->visible(fn ($record) => $record->name !== 'AdministratorAccess')
                    ->collapsed()
                    ->schema(function ($record) {
                        $features = AdmPermissionFeature::query()
                            ->where('permission_id', $record->id)
                            ->get()
                            ->pluck('feature_name')
                            ->toArray();
                        $resources = Filament::getResources();
                        $options = [];
                        $default = [];
                        foreach ($resources as $page) {
                            $tmp = class_basename($page);
                            $group = $page::getNavigationGroup() ?? 'その他';
                            $tmp = str_replace('Resource', '', $tmp);
                            $options[$group][$tmp] = $tmp;
                            if (in_array($tmp, $features)) {
                                $default[$group][] = $tmp;
                            }
                        }
                        $result = [];
                        foreach ($options as $group => $items) {
                            $result[] = Section::make()
                                ->schema([
                                    CheckboxList::make($group. 'Resource')
                                        ->label($group)
                                        ->options($items)
                                        ->columns(4)
                                        ->formatStateUsing(fn() => $default[$group] ?? [])
                                ])
                                ->label($group);
                        }
                        return $result;
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID'),
                Tables\Columns\TextColumn::make('name')->label('名前'),
                Tables\Columns\TextColumn::make('description')->label('説明'),
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
            'index' => AdmPermissionResource\Pages\ListAdmPermissions::route('/'),
            'create' => AdmPermissionResource\Pages\CreateAdmPermission::route('/create'),
            'edit' => AdmPermissionResource\Pages\EditAdmPermission::route('/{record}/edit'),
        ];
    }
}
