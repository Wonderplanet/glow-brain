<?php

namespace App\Filament\Resources;

use App\Constants\AdmPromotionTagFunctionName;
use App\Constants\DestinationInGamePath;
use App\Constants\DestinationType;
use App\Constants\DisplayFrequencyType;
use App\Constants\IgnDisplayType;
use App\Constants\NavigationGroups;
use App\Constants\SystemConstants;
use App\Entities\Clock;
use App\Facades\Promotion;
use App\Filament\Authorizable;
use App\Filament\Resources\IgnSettingResource\Pages;
use App\Models\Adm\AdmInformation;
use App\Models\Adm\AdmInGameNotice;
use App\Models\Adm\AdmUser;
use App\Models\Mng\MngInGameNotice;
use App\Models\Mst\MstEvent;
use App\Models\Mst\MstExchange;
use App\Models\Mst\MstShopItem;
use App\Models\Mst\MstShopPass;
use App\Models\Mst\OprGacha;
use App\Models\Opr\OprProduct;
use App\Services\ConfigGetService;
use App\Services\IgnService;
use App\Tables\Columns\DestinationInfoColumn;
use App\Tables\Columns\PeriodColumn;
use App\Tables\Columns\TipTapHtmlColumn;
use App\Traits\MngCacheDeleteTrait;
use App\Traits\RewardInfoGetTrait;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use FilamentTiptapEditor\Enums\TiptapOutput;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class IgnSettingResource extends Resource
{
    use Authorizable;
    use RewardInfoGetTrait;
    use MngCacheDeleteTrait;

    protected static ?string $model = MngInGameNotice::class;
    protected static ?string $navigationIcon = 'heroicon-o-information-circle';
    protected static ?string $label = "IGN";
    protected static ?string $navigationGroup = NavigationGroups::NOTICE->value;
    protected static bool $shouldRegisterNavigation = true;
    protected static ?int $navigationSort = 50; // メニューの並び順

    public static function canCreate(): bool
    {
        return !Promotion::isPromotionDestinationEnvironment();
    }

    public static function form(Form $form): Form
    {
        return $form->schema(static::getFormSchema());
    }

    public static function getFormSchema(): array
    {
        /** @var ConfigGetService $configGetService */
        $configGetService = app(ConfigGetService::class);

        $defaultDateString = (new CarbonImmutable())
            ->setTimezone($configGetService->getTimezone())
            ->setHour(0)
            ->setMinute(0)
            ->setSecond(0)
            ->format(SystemConstants::VIEW_DATETIME_FORMAT);

        $mstShopItems = MstShopItem::query()->get();
        $mstShopItemRewardRtos = $mstShopItems->map(function (MstShopItem $mstShopItem) {
            return $mstShopItem->reward;
        });
        $mstShopItemRewardInfos = self::getRewardInfos($mstShopItemRewardRtos) ?? collect();

        return [
            Section::make('掲載内容')->schema([
                TextInput::make('title')->label('タイトル')
                    ->afterStateHydrated(
                        function (Set $set, $record) {
                            $set('title', $record?->mng_in_game_notice_i18n?->title ?? '');
                        })
                    ->required(),

                TiptapEditor::make('description')->label('本文')
                    ->afterStateHydrated(
                        function (Set $set, $record) {
                            $set('description', $record?->mng_in_game_notice_i18n?->description ?? '');
                        })
                    ->output(TiptapOutput::Html)
                    ->disableFloatingMenus()
                    ->disableBubbleMenus()
                    ->tools(['bold', 'color'])
                    ->required(),

                Radio::make('display_type')->label('表示タイプ')
                    ->options(IgnDisplayType::labels()->toArray())
                    ->required()
                    ->columns(2)
                    ->reactive()
                    ->afterStateUpdated(
                        function ($state, Set $set) {
                            $set(
                                'is_image_required',
                                in_array(
                                    $state,
                                    [
                                        IgnDisplayType::BASIC_BANNER->value,
                                    ]
                                )
                            );
                        }
                    ),

                FileUpload::make('image')->label('バナー画像')
                    ->image()
                    ->required(fn (callable $get) => $get('is_image_required'))
                    ->saveUploadedFileUsing(function (UploadedFile $file, Set $set, $state) {
                        $fileName = $file?->getClientOriginalName() ?? '';
                        $set('upload_image_local_file_path', $file?->getRealPath() ?? '');
                        $set('upload_image_file_name', $fileName);
                    }),
                Hidden::make('upload_image_local_file_path'),
                Hidden::make('upload_image_file_name'),
            ]),

            Section::make('遷移先設定')->schema([
                Select::make('destination_type')->label('遷移先タイプ')
                    ->default(DestinationType::NONE->value)
                    ->required()
                    ->live()
                    ->options(DestinationType::labels()->toArray())
                    ->searchable()
                    ->reactive(), // reactiveを追加して、選択肢が変更されたときにフォームを再レンダリング
                TextInput::make('button_title')->label('ボタンタイトル')
                    ->afterStateHydrated(
                        function (Set $set, $record) {
                            $set('button_title', $record?->mng_in_game_notice_i18n?->button_title ?? '');
                        })
                    ->visible(fn (callable $get) => $get('destination_type') !== DestinationType::NONE->value)
                    ->required(),

                // ゲーム内遷移の追加フォーム
                Select::make('destination_path')->label('ゲーム内遷移先')
                    ->hidden(fn (callable $get) => $get('destination_type') !== DestinationType::IN_GAME->value)
                    ->required()
                    ->placeholder('--- 遷移先の機能を選択してください ---')
                    ->reactive()
                    ->searchable()
                    ->options(DestinationInGamePath::labels()->toArray()),
                Select::make('destination_path_detail')->label('ゲーム内遷移先詳細')
                    ->hidden(fn (callable $get) => $get('destination_type') !== DestinationType::IN_GAME->value || $get('destination_path') === DestinationInGamePath::PVP->value)
                    ->placeholder('--- 遷移先の詳細を選択してください ---')
                    ->searchable()
                    ->options(function (Get $get) use ($mstShopItemRewardInfos) {
                        switch ($get('destination_path')) {
                            case DestinationInGamePath::SHOP_PAID->value:
                                return OprProduct::query()
                                    ->get()
                                    ->mapWithKeys(function (OprProduct $oprProduct) {
                                        return [$oprProduct->id => sprintf(
                                            '[%s] %s',
                                            $oprProduct->id,
                                            $oprProduct->getProductInfoAttribute()
                                        )];
                                    })
                                    ->toArray();
                            case DestinationInGamePath::SHOP_FREE->value:
                                return MstShopItem::query()
                                    ->get()
                                    ->mapWithKeys(function (MstShopItem $mstShopItem) use ($mstShopItemRewardInfos) {
                                        $rewardInfo = $mstShopItemRewardInfos->get($mstShopItem->id);
                                        return [$mstShopItem->id => sprintf(
                                            '[%s] %s (%s)',
                                            $mstShopItem->id,
                                            $rewardInfo?->getName() ?? 'N/A',
                                            $rewardInfo?->getAmount() ?? 'N/A'
                                        )];
                                    })
                                    ->toArray();
                            case DestinationInGamePath::PASS->value:
                                return MstShopPass::query()
                                    ->get()
                                    ->mapWithKeys(function (MstShopPass $mstShopPass) {
                                        return [$mstShopPass->id => sprintf(
                                            '[%s] %s',
                                            $mstShopPass->id,
                                            $mstShopPass->mst_shop_pass_i18n?->name ?? ''
                                        )];
                                    })
                                    ->toArray();
                            case DestinationInGamePath::GACHA->value:
                                return OprGacha::query()
                                    ->get()
                                    ->mapWithKeys(function (OprGacha $oprGacha) {
                                        return [$oprGacha->id => sprintf(
                                            '[%s] %s',
                                            $oprGacha->id,
                                            $oprGacha->opr_gacha_i18n->name
                                        )];
                                    })
                                    ->toArray();
                            case DestinationInGamePath::EVENT->value:
                                return MstEvent::query()
                                    ->get()
                                    ->mapWithKeys(function (MstEvent $mstEvent) {
                                        return [$mstEvent->id => sprintf(
                                            '[%s] %s',
                                            $mstEvent->id,
                                            $mstEvent->mst_event_i18n->name ?? ''
                                        )];
                                    })
                                    ->toArray();
                            case DestinationInGamePath::NOTICE->value:
                                return AdmInformation::query()
                                    ->get()
                                    ->mapWithKeys(function (AdmInformation $admInformation) {
                                        return [$admInformation->id => sprintf(
                                            '[%s] %s',
                                            $admInformation->getCategoryLabelAttribute(),
                                            $admInformation->title
                                        )];
                                    })
                                    ->toArray();
                            case DestinationInGamePath::EXCHANGE->value:
                                return MstExchange::query()
                                    ->get()
                                    ->mapWithKeys(function (MstExchange $mstExchange) {
                                        return [$mstExchange->id => sprintf(
                                            '[%s] %s',
                                            $mstExchange->id,
                                            $mstExchange->getName()
                                        )];
                                    })
                                    ->toArray();
                            case DestinationInGamePath::PVP->value:
                                // PVPでは詳細設定を空にして、ユーザー入力を許可しない
                                return [];
                            default:
                                return [];
                        }
                    }),

                // 外部サイト遷移の追加フォーム
                TextInput::make('destination_path')->label('外部サイトURL')
                    ->hidden(fn (callable $get) => $get('destination_type') !== DestinationType::WEB->value)
                    ->required(),

            ])->columns(2),

            Section::make('掲載方法')->schema([
                DateTimePicker::make('start_at')->label('掲載開始 (JST)')
                    ->afterStateHydrated(
                        function (Set $set, $record) use ($defaultDateString) {
                            $set('start_at', $record?->formatted_start_at ?? $defaultDateString);
                        })
                    ->required(),

                DateTimePicker::make('end_at')->label('掲載終了 (JST)')
                    ->afterStateHydrated(
                        function (Set $set, $record) use ($defaultDateString) {
                            $set('end_at', $record?->formatted_end_at ?? $defaultDateString);
                        })
                    ->after('start_at')
                    ->required(),

                TextInput::make('priority')->label('表示優先度')
                    ->numeric()
                    ->default(1)
                    ->required(),

                Select::make('display_frequency_type')->label('表示頻度')
                    ->required()
                    ->placeholder('--- 表示頻度を選択してください ---')
                    ->live()
                    ->options(DisplayFrequencyType::labels()->toArray()),

                Promotion::getTagSelectForm(),

            ])->columns(2),
        ];
    }

    public static function table(Table $table): Table
    {
        /** @var Clock $clock */
        $clock = app(Clock::class);
        $now = $clock->now();

        /** @var IgnService $ignService */
        $ignService = app(IgnService::class);

        $authorAdmUserIds = AdmInGameNotice::query()
            ->get()
            ->pluck('author_adm_user_id')
            ->unique()
            ->toArray();
        $admUserOptions = AdmUser::query()
            ->whereIn('id', $authorAdmUserIds)
            ->get()
            ->mapWithKeys(fn ($admUser) => [$admUser->id => $admUser->name])
            ->toArray();

        return $table
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->recordUrl(function ($record) { return null; })
            ->columns([
                Tables\Columns\TextColumn::make('status')->label('ステータス')
                    ->state(function (MngInGameNotice $record) use ($now) {
                        return $record->calcStatus($now);
                    })
                    ->sortable()
                    ->searchable()
                    ->badge(true)
                    ->color(function (MngInGameNotice $record) use ($now) {
                        return $record->calcStatusBadgeColor($now);
                    }),

                Tables\Columns\TextColumn::make('mng_in_game_notice_i18n.title')->label('タイトル')
                    ->searchable(),

                Tables\Columns\TextColumn::make('adm_promotion_tag_id')
                    ->label('昇格タグID')
                    ->searchable()
                    ->sortable(),

                TipTapHtmlColumn::make('description_html')->label('本文'),

                Tables\Columns\ImageColumn::make('banner')->label('バナー画像')
                    ->getStateUsing(function (MngInGameNotice $record) use ($ignService) : string {
                        return $ignService->makeBannerUrl($record?->mng_in_game_notice_i18n?->banner_url);
                    })
                    ->extraImgAttributes(
                        ['style' => 'height: 5em; width: auto; object-fit: cover;']
                    ),

                PeriodColumn::make('post_period')->label('掲載期間')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('display_frequency_type_label')->label('表示頻度')
                    ->searchable(),

                DestinationInfoColumn::make('destination_info')->label('遷移先'),

                Tables\Columns\TextColumn::make('author_name')->label('作成者')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('priority')->label('表示優先度')
                    ->searchable(),
            ])
            ->defaultSort('priority', 'desc')
            ->filters([
                Filter::make('title')->label('タイトル')
                    ->form([
                        TextInput::make('title')->label('タイトル')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['title'])) {
                            return $query;
                        }
                        return $query->whereHas('mng_in_game_notice_i18n', function ($query) use ($data) {
                            $query->where('title', 'like', "%{$data['title']}%");
                        });
                    }),

                Filter::make('description')->label('本文')
                    ->form([
                        TextInput::make('description')->label('本文')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['description'])) {
                            return $query;
                        }
                        return $query->whereHas('mng_in_game_notice_i18n', function ($query) use ($data) {
                            $query->where('description', 'like', "%{$data['description']}%");
                        });
                    }),

                Promotion::getTagSelectFilter(),

                SelectFilter::make('author_adm_user_name')->label('作成者名')
                    ->options($admUserOptions)
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        $admUserId = $data['value'];
                        if (blank($admUserId)) {
                            return $query;
                        }

                        $admUser = AdmUser::query()->where('id', $admUserId)->first();
                        if (is_null($admUser)) {
                            // 何も該当しなかったクエリにする
                            return $query->whereRaw('1 = 0');
                        }

                        $admInGameNotices = AdmInGameNotice::query()
                            ->where('author_adm_user_id', $admUser->id)
                            ->get()
                            ->pluck('mng_in_game_notice_id');

                        return $query->whereIn('id', $admInGameNotices);
                    }),

                SelectFilter::make('status')->label('ステータス')
                    ->options([
                        'draft' => '下書き',
                        'before' => '掲載前',
                        'during' => '掲載中',
                        'after' => '掲載終了',
                    ])
                    ->query(fn (Builder $query, $data) => match ($data['value']) {
                        'draft' => $query->where('enable', 0),
                        'before' => $query->where('start_at', '>', CarbonImmutable::now()),
                        'during' => $query->where('start_at', '<=', CarbonImmutable::now())
                            ->where('end_at', '>=', CarbonImmutable::now()),
                        'after' => $query->where('end_at', '<', CarbonImmutable::now()),
                        default => $query,
                    }),
                ], FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn (Action $action) => $action->label('適用'),
            )
            ->headerActions(
                Promotion::getHeaderActions(
                    AdmPromotionTagFunctionName::IGN,
                    function (string $environment, string $admPromotionTagId) use ($ignService) {
                        $ignService->import(
                            $environment,
                            $admPromotionTagId,
                        );
                    }
                ),
            )
            ->actions(self::getActions(), position: ActionsPosition::BeforeColumns)
            ->emptyStateActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getActions(): array
    {
        if (Promotion::isPromotionDestinationEnvironment()) {
            return [];
        }

        return [
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make()
                ->using(function (MngInGameNotice $record) {
                    $ignService = app(IgnService::class);
                    $ignService->deleteIgn($record);
                }),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIgnSettings::route('/'),
            'create' => Pages\CreateIgnSetting::route('/create'),
            'edit' => Pages\EditIgnSetting::route('/{record}/edit'),
        ];
    }
}
