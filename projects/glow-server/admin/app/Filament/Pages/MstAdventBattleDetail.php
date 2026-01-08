<?php

namespace App\Filament\Pages;

use App\Constants\AdventBattleRewardCategory;
use App\Constants\InGameContentType;
use App\Domain\Resource\Mst\Models\MstStageEventRule;
use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Filament\Resources\MstAdventBattleResource;
use App\Models\Mst\MstAdventBattle;
use App\Models\Mst\MstAdventBattleRank;
use App\Models\Mst\MstAdventBattleReward;
use App\Models\Mst\MstAdventBattleRewardGroup;
use App\Models\Mst\MstInGameSpecialRule;
use App\Traits\RewardInfoGetTrait;
use App\Utils\StringUtil;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Infolist;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class MstAdventBattleDetail extends MstDetailBasePage implements HasTable
{
    use RewardInfoGetTrait;
    use InteractsWithTable;
    use InteractsWithInfolists;

    protected static string $view = 'filament.pages.mst-advent-battle-detail';

    protected static ?string $title = '降臨バトル詳細';

    public string $mstAdventBattleId = '';

    protected $queryString = [
        'mstAdventBattleId',
    ];

    protected function getResourceClass(): ?string
    {
        return MstAdventBattleResource::class;
    }

    protected function getMstModelByQuery(): ?MstAdventBattle
    {
        return MstAdventBattle::query()->where('id', $this->mstAdventBattleId)->first();
    }

    protected function getMstNotFoundDangerNotificationBody(): string
    {
        return sprintf('降臨バトルID: %s', $this->mstAdventBattleId);
    }

    protected function getSubTitle(): string
    {
        return StringUtil::makeIdNameViewString(
            $this->mstAdventBattleId,
            '',
        );
    }

    public function table(Table $table): Table
    {
        $query = MstAdventBattle::query();

        return $table
            ->query($query)
            ->searchable(false)
            ->columns([
                TextColumn::make('id')
                    ->label('Id')
                    ->searchable()
                    ->sortable(),
            ]);
    }

    public function infoList(): Infolist
    {
        $mstAdventBattle = $this->getMstModel();

        $state = [
            'id' => $mstAdventBattle->id,
            'advent_battle_type' => $mstAdventBattle->advent_battle_type,
            'mst_stage_rule_group_id' => $mstAdventBattle->mst_stage_rule_group_id,
            'challengeable_count' => $mstAdventBattle->challengeable_count,
            'ad_challengeable_count' => $mstAdventBattle->ad_challengeable_count,
            'time_limit_seconds' => $mstAdventBattle->time_limit_seconds,
            'display_mst_unit_id1' => $mstAdventBattle->display_mst_unit_id1,
            'display_mst_unit_id2' => $mstAdventBattle->display_mst_unit_id2,
            'display_mst_unit_id3' => $mstAdventBattle->display_mst_unit_id3,
            'start_at' => $mstAdventBattle->start_at,
            'end_at' => $mstAdventBattle->end_at,
            'release_key' => $mstAdventBattle->release_key,
        ];
        $fieldset = Fieldset::make('降臨バトル詳細')
            ->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('advent_battle_type')->label('降臨バトルタイプ'),
                TextEntry::make('challengeable_count')->label('1日の挑戦回数'),
                TextEntry::make('ad_challengeable_count')->label('1日の広告視聴での挑戦可能回数'),
                TextEntry::make('time_limit_seconds')->label('制限時間秒'),
                TextEntry::make('display_mst_unit_id1')->label('降臨バトルトップ場所1に表示するキャラ'),
                TextEntry::make('display_mst_unit_id2')->label('降臨バトルトップ場所2に表示するキャラ'),
                TextEntry::make('display_mst_unit_id3')->label('降臨バトルトップ場所3に表示するキャラ'),
                TextEntry::make('start_at')->label('開始日'),
                TextEntry::make('end_at')->label('終了日'),
                TextEntry::make('release_key')->label('リリースキー'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function adventBattleRankTable(): ?Table
    {
        $query = MstAdventBattleRank::query()
            ->where('mst_advent_battle_id', $this->mstAdventBattleId);

        return $this->getTable()
            ->heading('ランク情報')
            ->query($query)
            ->columns([
                TextColumn::make('id')->label('ID'),
                TextColumn::make('rank_type')->label('バトルランクタイプ'),
                TextColumn::make('rank_level')->label('ランクレベル'),
                TextColumn::make('required_lower_score')->label('ランクレベル到達に必要な最低スコア'),
            ])
            ->paginated(false);
    }

    public function inGameSpecialRuleTable(): array
    {
        $mstAdventBattle = $this->getMstModel();

        $mstInGameSpecialRules = MstInGameSpecialRule::query()
            ->where('target_id', $mstAdventBattle?->id)
            ->where('content_type', InGameContentType::ADVENT_BATTLE->value)
            ->get();

        $mstInGameSpecialRuleTableRows = [];
        foreach ($mstInGameSpecialRules as $mstInGameSpecialRule) {
            $mstInGameSpecialRuleTableRows[] = [
                'ID' => $mstInGameSpecialRule->id,
                '編成条件' => $mstInGameSpecialRule->rule_type,
                '編成条件値' => $mstInGameSpecialRule->rule_value,
            ];
        }

        return $mstInGameSpecialRuleTableRows;
    }

    public function adventBattleRewardTable($rewardCategoryType): array
    {
        $mstAdventBattleRewardGroups = MstAdventBattleRewardGroup::query()
            ->where('mst_advent_battle_id', $this->mstAdventBattleId)
            ->get();

        $mstAdventBattleRewardGroupIds = $mstAdventBattleRewardGroups->pluck('reward_category', 'id')->toArray();

        $rewardDtoList = MstAdventBattleReward::query()->get()->map(function (MstAdventBattleReward $mstAdventBattleRewards) {
            return $mstAdventBattleRewards->reward;
        });

        $rewardInfos = $this->getRewardInfos($rewardDtoList);

        $mstAdventBattleRewards = MstAdventBattleRewardGroup::query()
            ->with([
                'mst_advent_battle_rewards'
            ])
            ->whereIn('id', array_keys($mstAdventBattleRewardGroupIds, $rewardCategoryType))
            ->get();

        $conditionName = '';
        switch ($rewardCategoryType) {
            case AdventBattleRewardCategory::MAX_SCORE->value :
                $conditionName = '最大スコア';
                break;
            case AdventBattleRewardCategory::RANKING->value :
                $conditionName = '順位';
                break;
            case AdventBattleRewardCategory::RANK->value :
                $conditionName = 'ランク';
                $mstAdventBattleRanks = MstAdventBattleRank::query()
                    ->where('mst_advent_battle_id', $this->mstAdventBattleId)
                    ->get()
                    ->keyBy('id');
                break;
            case AdventBattleRewardCategory::RAID_TOTAL_SCORE->value :
                $conditionName = 'スコア';
                break;
        }

        $adventBattleRewardTableRows = [];
        foreach ($mstAdventBattleRewards as $mstAdventBattleReward) {
            $rewards = [];
            foreach ($mstAdventBattleReward->mst_advent_battle_rewards as $mst_advent_battle_reward) {
                $rewards[] = $rewardInfos->get($mst_advent_battle_reward->id);
            }

            if ($rewardCategoryType === AdventBattleRewardCategory::RANK->value) {
                $mstAdventBattleRank = $mstAdventBattleRanks->get($mstAdventBattleReward->condition_value);
                $adventBattleRewardTableRows[] = [
                    'ランク' => $mstAdventBattleRank?->rank_type ?? '',
                    '報酬情報' => $rewards,
                ];
            } else {
                $adventBattleRewardTableRows[] = [
                    $conditionName => $mstAdventBattleReward->condition_value,
                    '報酬情報' => $rewards,
                ];
            }
        }
        return $adventBattleRewardTableRows;
    }
}
