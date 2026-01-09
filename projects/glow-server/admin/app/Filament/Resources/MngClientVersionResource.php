<?php

namespace App\Filament\Resources;

use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Filament\Resources\MngClientVersionResource\Pages;
use App\Models\Mng\MngClientVersion;
use App\Traits\MngCacheDeleteTrait;
use Closure;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;
use WonderPlanet\Domain\Common\Constants\PlatformConstant;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Constants\MasterAssetReleaseConstants;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Utils\ClientCompatibilityVersionUtility;

class MngClientVersionResource extends Resource
{
    use Authorizable;
    use MngCacheDeleteTrait;

    protected static ?string $model = MngClientVersion::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $label = "クライアントバージョン管理";
    protected static ?string $navigationGroup = NavigationGroups::OTHER->value;
    protected static bool $shouldRegisterNavigation = true;
    protected static ?int $navigationSort = 110;

    public static function form(Form $form): Form
    {
        return $form->schema(static::getFormSchema());
    }

    public static function getFormSchema(): array
    {
        // クライアントバージョンのバリデーションメソッド
        $validateClientVersion = ClientCompatibilityVersionUtility::makeValidateClientCompatibilityVersion(null);

        return [
            Section::make('基本情報')->schema([
                Select::make('platform')
                    ->label('プラットフォーム')
                    ->required()
                    ->disabledOn('edit')
                    ->default(MasterAssetReleaseConstants::PLATFORM_ALL)
                    ->options(MasterAssetReleaseConstants::PLATFORM_STRING_LIST)
                    ->rules([
                        fn () => function ($attribute, $value, Closure $fail) {
                            if (!array_key_exists($value, MasterAssetReleaseConstants::PLATFORM_STRING_LIST)) {
                                $fail('想定しないplatformが選択されています');
                            }
                        }
                    ])
                    ->live(),

                TextInput::make('client_version')
                    ->label('クライアントバージョン')
                    ->hint('セマンティックバージョン形式（例: 1.2.3）')
                    ->required()
                    ->disabledOn('edit')
                    ->maxLength(32)
                    ->rules([
                        'regex:/^\d+\.\d+\.\d+$/',
                        fn () => function ($attribute, $value, Closure $fail) use ($validateClientVersion) {
                            // クライアント互換性バージョンのバリデーションを実行
                            $validateClientVersion($attribute, $value, $fail);
                        },
                    ])
                    ->validationMessages([
                        'regex' => '「数字.数字.数字」の形式で入力してください',
                    ])
                    ->unique(modifyRuleUsing: function (Unique $rule, Get $get) {
                        $platform = (int) $get('platform');
                        if ($platform === MasterAssetReleaseConstants::PLATFORM_ALL) {
                            return $rule->whereIn('platform', [PlatformConstant::PLATFORM_IOS, PlatformConstant::PLATFORM_ANDROID]);
                        }
                        return $rule->where('platform', $platform);
                    }, ignoreRecord: true)
                    ->validationMessages(['unique' => 'すでに登録済みのクライアントバージョンです']),

                Toggle::make('is_force_update')
                    ->label('強制アップデート')
                    ->helperText('有効にすると、このバージョンのユーザーは強制的にアップデートが必要になります')
                    ->default(false),
            ]),
        ];
    }

    public static function table(Table $table): Table
    {
        $query = MngClientVersion::query()
            ->orderByRaw('CAST(SUBSTRING_INDEX(client_version, \'.\', 1) AS UNSIGNED) DESC')
            ->orderByRaw('CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(client_version, \'.\', 2), \'.\', -1) AS UNSIGNED) DESC')
            ->orderByRaw('CAST(SUBSTRING_INDEX(client_version, \'.\', -1) AS UNSIGNED) DESC');

        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->recordUrl(function ($record) {
                return null;
            })
            ->columns([
                TextColumn::make('client_version')
                    ->label('クライアントバージョン')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('platform_label')
                    ->label('プラットフォーム')
                    ->badge()
                    ->color(function (MngClientVersion $record): string {
                        return $record->getPlatformColor();
                    })
                    ->sortable(),

                TextColumn::make('is_force_update_label')
                    ->label('強制アップデート')
                    ->badge()
                    ->color(function (MngClientVersion $record): string {
                        return $record->getIsForceUpdateColor();
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('作成日時')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('更新日時')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('platform')
                    ->label('プラットフォーム')
                    ->options(PlatformConstant::PLATFORM_STRING_LIST),

                SelectFilter::make('is_force_update')
                    ->label('強制アップデート')
                    ->options(MngClientVersion::IS_FORCE_UPDATE_OPTIONS)
                    ->native(false),

                Filter::make('client_version')
                    ->form([
                        TextInput::make('client_version')
                            ->label('クライアントバージョン')
                            ->placeholder('1.2.3'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['client_version'],
                                fn (Builder $query, $version): Builder => $query->where('client_version', 'like', "%{$version}%"),
                            );
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->deferFilters()
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->after(function (MngClientVersion $record) {
                        static::deleteMngClientVersionCache();
                    }),
            ], position: ActionsPosition::BeforeColumns);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMngClientVersions::route('/'),
            'create' => Pages\CreateMngClientVersion::route('/create'),
            'edit' => Pages\EditMngClientVersion::route('/{record}/edit'),
        ];
    }
}
