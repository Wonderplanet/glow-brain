<?php

namespace App\Filament\Pages;

use App\Constants\MstDataMenuDisplayOrder;
use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Models\Mst\MstIdleIncentive;
use App\Models\Mst\MstIdleIncentiveReward;
use App\Tables\Columns\MstIdColumn;
use App\Utils\StringUtil;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Infolist;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;

class MstIdleIncentiveDetail extends MstDetailBasePage implements HasTable
{
    use InteractsWithTable;
    use InteractsWithInfolists;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_VIEWER->value;
    protected static string $view = 'filament.pages.mst-idle-incentive-detail';
    protected static ?string $title = '探索';
    protected static ?int $navigationSort = MstDataMenuDisplayOrder::IDLE_INCENTIVE_DISPLAY_ORDER->value; // メニューの並び順

    protected static bool $shouldRegisterNavigation = true;

    public string $mstIdleIncentiveId = '';

    protected $queryString = [
        'mstIdleIncentiveId',
    ];

    protected function getResourceClass(): ?string
    {
        // 一覧ページがない詳細ページのため、Resourceは指定しない
        return null;
    }

    protected function getMstModelByQuery(): ?MstIdleIncentive
    {
        return MstIdleIncentive::query()->first();
    }

    protected function getMstNotFoundDangerNotificationBody(): string
    {
        return '探索(mst_idle_incentives)のデータがありません。';
    }

    protected function getSubTitle(): string
    {
        return StringUtil::makeIdNameViewString(
            $this->getMstModel()?->id ?? '',
            '探索',
        );
    }

    public static function getMainTitle()
    {
        return self::$title;
    }

    public function infoList(): InfoList
    {
        $mstIdleIncentive = MstIdleIncentive::query()->first();

        $state = [
            // 基本情報
            'id' => $mstIdleIncentive->id,
            'release_key' => $mstIdleIncentive->release_key,
            'initial_reward_receive_minutes' => $mstIdleIncentive->initial_reward_receive_minutes . '分',
            'reward_increase_interval_minutes' => $mstIdleIncentive->reward_increase_interval_minutes . '分',
            'max_idle_hours' => $mstIdleIncentive->max_idle_hours . '時間',
            'quick_idle_minutes' => $mstIdleIncentive->quick_idle_minutes . '分',

            // クイック探索 プリズム
            'required_quick_receive_diamond_amount' => $mstIdleIncentive->required_quick_receive_diamond_amount . '回',
            'max_daily_diamond_quick_receive_amount' => $mstIdleIncentive->max_daily_diamond_quick_receive_amount . '個',
            // クイック探索 広告視聴
            'max_daily_ad_quick_receive_amount' => $mstIdleIncentive->max_daily_ad_quick_receive_amount . '回',
            'ad_interval_seconds' => $mstIdleIncentive->ad_interval_seconds . '秒',
        ];

        return $this->makeInfolist()
            ->state($state)
            ->schema([
                Section::make('基本情報')
                    ->schema([
                        TextEntry::make('id')->label('ID'),
                        TextEntry::make('release_key')->label('リリースキー'),
                        TextEntry::make('initial_reward_receive_minutes')->label('報酬が受取可能になるまでの時間'),
                        TextEntry::make('reward_increase_interval_minutes')->label('報酬が増加する間隔'),
                        TextEntry::make('max_idle_hours')->label('最大放置時間'),
                        TextEntry::make('quick_idle_minutes')->label('クイック探索時の放置時間'),
                    ])
                    ->columns(2),
                Section::make('クイック探索 プリズム消費')
                    ->schema([
                        TextEntry::make('required_quick_receive_diamond_amount')->label('1日あたりの最大回数'),
                        TextEntry::make('max_daily_diamond_quick_receive_amount')->label('プリズムの消費数'),
                    ])
                    ->columns(2),
                Section::make('クイック探索 広告視聴')
                    ->schema([
                        TextEntry::make('max_daily_ad_quick_receive_amount')->label('1日あたりの最大回数'),
                        TextEntry::make('ad_interval_seconds')->label('広告視聴のインターバル'),
                    ])
                    ->columns(2),
            ]);
    }
    public function table(Table $table): Table
    {
        $query = MstIdleIncentiveReward::query();

        return $table
            ->searchable(false)
            ->query($query)
            ->hiddenFilterIndicators()
            ->columns([
                MstIdColumn::make('mst_stage_info')->label('ステージ情報')
                    ->getMstUsing(function (MstIdleIncentiveReward $mstIdleIncentiveReward) {
                        return $mstIdleIncentiveReward->mst_stage;
                    })
                    ->getMstDetailPageUrlUsing(function (MstIdleIncentiveReward $mstIdleIncentiveReward) {
                        return StageDetail::getUrl([
                            'stageId' => $mstIdleIncentiveReward->mst_stage->id,
                        ]);
                    }),
                TextColumn::make('base_exp_amount')->label('経験値のベース獲得量'),
                TextColumn::make('base_coin_amount')->label('コインのベース獲得量'),
            ])
            ->filters([
                \Filament\Tables\Filters\Filter::make('mst_stage_id')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('mst_stage_id')
                            ->label('ステージID')
                    ])
                    ->query(function ($query, $data) {
                        if (blank($data['mst_stage_id'])) {
                            return $query;
                        }
                        return $query->where('mst_stage_id', 'like', '%' . $data['mst_stage_id'] . '%');
                    })
                ], FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn (Action $action) => $action
                    ->label('適用'),
            )
            ->actions([
                Action::make('detail')
                    ->label('報酬詳細')
                    ->button()
                    ->url(function (MstIdleIncentiveReward $mstIdleIncentiveReward) {
                        return MstIdleIncentiveRewardDetail::getUrl([
                            'mstStageId' => $mstIdleIncentiveReward->mst_stage->id,
                        ]);
                    }),
            ], position: ActionsPosition::BeforeColumns);
    }
}
