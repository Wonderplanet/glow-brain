<?php

namespace App\Filament\Resources;

use App\Constants\AdmPromotionTagFunctionName;
use App\Constants\NavigationGroups;
use App\Constants\RewardType;
use App\Constants\SystemConstants;
use App\Facades\Promotion;
use App\Filament\Authorizable;
use App\Filament\Resources\MngJumpPlusRewardScheduleResource\Pages;
use App\Models\Mng\MngJumpPlusRewardSchedule;
use App\Services\JumpPlusRewardService;
use App\Tables\Columns\RewardInfoColumn;
use App\Traits\MngCacheDeleteTrait;
use App\Traits\RewardInfoGetTrait;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use App\Tables\Columns\PeriodColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class MngJumpPlusRewardScheduleResource extends Resource
{
    use Authorizable;
    use MngCacheDeleteTrait;
    use RewardInfoGetTrait;

    protected static ?string $model = MngJumpPlusRewardSchedule::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $label = "ジャンプ+連携報酬";
    protected static ?string $navigationGroup = NavigationGroups::OTHER->value;
    protected static bool $shouldRegisterNavigation = true;
    protected static ?int $navigationSort = 100; // メニューの並び順（IGNの後）

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
        $defaultDateString = (new CarbonImmutable())
            ->setTimezone(SystemConstants::TIMEZONE_UTC)
            ->setHour(0)
            ->setMinute(0)
            ->setSecond(0)
            ->format(SystemConstants::VIEW_DATETIME_FORMAT);

        return [
            Section::make('基本情報')->schema([
                TextInput::make('id')
                    ->label('報酬ID')
                    ->hint('連携サイトで使用するIDです')
                    ->required()
                    ->disabledOn('edit')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Promotion::getTagSelectForm(),
            ]),

            Section::make('スケジュール設定')->schema([
                DateTimePicker::make('start_at')
                    ->label('開始日時 (JST)')
                    ->afterStateHydrated(
                        function (Set $set, $record) use ($defaultDateString) {
                            $set('start_at', $record?->start_at->toDateTimeString() ?? $defaultDateString);
                        }
                    )
                    ->required(),

                DateTimePicker::make('end_at')
                    ->label('終了日時 (JST)')
                    ->afterStateHydrated(
                        function (Set $set, $record) use ($defaultDateString) {
                            $set('end_at', $record?->end_at->toDateTimeString() ?? $defaultDateString);
                        }
                    )
                    ->after('start_at')
                    ->required(),
            ])->columns(2),

            Section::make('報酬設定')
                ->description('このスケジュールで配布する報酬を設定します')
                ->schema([
                    Repeater::make('rewards')
                        ->columnSpanFull()
                        ->label(new HtmlString('<p class="font-bold text-xl">' . '報酬設定' . '</p>'))
                        ->schema([
                            Select::make('resource_type')
                                ->label('報酬タイプ')
                                ->placeholder('オプションを選択')
                                ->options(fn () => self::getSelectableRewardTypes())
                                ->reactive(),
                            Select::make('resource_id')
                                ->label('リソースID')
                                ->placeholder('オプションを選択')
                                ->searchable()
                                ->disabled(function (callable $get) {
                                    return self::disableResourceIdRewardType($get('resource_type'));
                                })
                                ->options(function (callable $get) {
                                    // 選択したアイテムタイプに応じてIDを表示する
                                    return self::getRewardResourceIds($get('resource_type'));
                                })
                                ->reactive(),
                            TextInput::make('resource_amount')
                                ->label('個数')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->reactive(),
                        ])
                        ->columns(4)
                        ->addActionLabel('報酬追加')
                        ->reorderableWithButtons(),
                ])
                ->collapsible(),
        ];
    }

    private static function getSelectableRewardTypes(): Collection
    {
        $cases = [
            RewardType::FREE_DIAMOND,
            RewardType::COIN,
            RewardType::ITEM,
            RewardType::UNIT,
            RewardType::EMBLEM,
        ];

        $labels = collect();
        foreach ($cases as $case) {
            $labels->put($case->value, $case->label());
        }
        return $labels;
    }

    public static function table(Table $table): Table
    {
        $now = CarbonImmutable::now();

        $jumpPlusRewardService = app(JumpPlusRewardService::class);

        return $table
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->recordUrl(function ($record) {
                return null;
            })
            ->columns([
                TextColumn::make('status')
                    ->label('期間ステータス')
                    ->searchable()
                    ->getStateUsing(
                        function (MngJumpPlusRewardSchedule $schedule) use ($now) {
                            return $schedule->calcStatus($now);
                        }
                    )
                    ->badge(true)
                    ->color(function (MngJumpPlusRewardSchedule $schedule) use ($now) {
                        return $schedule->calcStatusBadgeColor($now);
                    }),

                TextColumn::make('id')
                    ->label('報酬ID')
                    ->tooltip('連携サイトで使用するIDです')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('adm_promotion_tag_id')
                    ->label('昇格タグID')
                    ->searchable()
                    ->sortable(),

                PeriodColumn::make('period')
                    ->label('スケジュール期間')
                    ->sortable(false)
                    ->searchable(),


                RewardInfoColumn::make('reward_infos')
                    ->label('報酬情報')
                    ->getStateUsing(
                        fn($record) => self::getRewardInfos($record->mng_jump_plus_rewards->map->reward)
                    ),

                // upadtead_atを最終更新日時として
                TextColumn::make('updated_at')
                    ->label('最終更新日時')
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('start_at', 'desc')
            ->filters([
                Filter::make('id')->label('報酬ID')
                    ->form([
                        TextInput::make('id')->label('報酬ID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['id'])) {
                            return $query;
                        }
                        return $query->where('id', 'like', "%{$data['id']}%");
                    }),


                Promotion::getTagSelectFilter(),
            ], FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn(Tables\Actions\Action $action) => $action->label('適用'),
            )
            ->headerActions(
                Promotion::getHeaderActions(
                    AdmPromotionTagFunctionName::JUMP_PLUS_REWARD,
                    function (string $environment, string $admPromotionTagId) use ($jumpPlusRewardService) {
                        $jumpPlusRewardService->import(
                            $environment,
                            $admPromotionTagId,
                        );
                    }
                ),
            )
            ->actions(self::getActions(), position: ActionsPosition::BeforeColumns)
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getActions(): array
    {
        if (Promotion::isPromotionDestinationEnvironment()) {
            return [];
        }

        /** @var JumpPlusRewardService $jumpPlusRewardService */
        $jumpPlusRewardService = app(JumpPlusRewardService::class);

        return [
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make()
                ->using(function (MngJumpPlusRewardSchedule $record) use ($jumpPlusRewardService) {
                    $jumpPlusRewardService->deleteMngJumpPlusRewardSchedule($record);
                }),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMngJumpPlusRewardSchedules::route('/'),
            'create' => Pages\CreateMngJumpPlusRewardSchedule::route('/create'),
            'edit' => Pages\EditMngJumpPlusRewardSchedule::route('/{record}/edit'),
        ];
    }
}
