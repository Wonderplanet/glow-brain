<?php

namespace App\Filament\Pages;

use App\Constants\PvpConstant;
use App\Constants\PvpRewardCategory;
use App\Constants\PvpTab;
use App\Domain\Resource\Mst\Models\MstPvpRank;
use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Models\Mst\MstPvp;
use App\Models\Mst\MstPvpReward;
use App\Models\Mst\MstPvpRewardGroup;
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

class MstPvpDetail extends MstDetailBasePage implements HasTable
{
    use RewardInfoGetTrait;
    use InteractsWithTable;
    use InteractsWithInfolists;

    protected static string $view = 'filament.pages.mst-pvp-detail';

    protected static ?string $title = 'ランクマッチ詳細';

    public string $mstPvpId = '';

    protected $queryString = [
        'mstPvpId',
    ];

    protected function getResourceClass(): ?string
    {
        return MstPvps::class;
    }

    protected function getAdditionalBreadcrumbs(): array
    {
        $mstPvp = $this->getMstModel();
        if ($mstPvp === null) {
            return [];
        }

        return [
            MstPvps::getUrl() => PvpTab::PVP_LIST,
        ];
    }

    protected function getMstModelByQuery(): ?MstPvp
    {
        $mstPvp = MstPvp::query()->where('id', $this->mstPvpId)->first();
        if ($mstPvp === null) {
            $this->mstPvpId = PvpConstant::DEFAULT_MST_PVP_ID;
            $mstPvp = MstPvp::query()->where('id', $this->mstPvpId)->first();;
        }
        return $mstPvp;
    }

    protected function getMstNotFoundDangerNotificationBody(): string
    {
        return sprintf('ランクマッチID: %s', $this->mstPvpId);
    }

    protected function getSubTitle(): string
    {
        return StringUtil::makeIdNameViewString(
            $this->mstPvpId,
            $this->getMstModel()?->getName() ?? '',
        );
    }

    public function table(Table $table): Table
    {
        $query = MstPvp::query();

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
        $mstPvp = $this->getMstModel();

        $state = [
            'id' => $mstPvp->id,
            'ranking_min_pvp_rank_class' => $mstPvp->ranking_min_pvp_rank_class,
            'max_daily_challenge_count' => $mstPvp->max_daily_challenge_count,
            'max_daily_item_challenge_count' => $mstPvp->max_daily_item_challenge_count,
            'item_challenge_cost_amount' => $mstPvp->item_challenge_cost_amount,
            'description' => $mstPvp?->mst_pvp_i18n?->description ?? "",
            'release_key' => $mstPvp->release_key,
        ];
        $fieldset = Fieldset::make('ランクマッチ詳細')
            ->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('ranking_min_pvp_rank_class')->label('ランキングに含む最小PVPランク区分'),
                TextEntry::make('max_daily_challenge_count')->label('1日のアイテム消費なし挑戦可能回数'),
                TextEntry::make('max_daily_item_challenge_count')->label('1日のアイテム消費あり挑戦可能回数'),
                TextEntry::make('item_challenge_cost_amount')->label('アイテム消費あり挑戦時の消費アイテム数'),
                TextEntry::make('description')->label('説明'),
                TextEntry::make('release_key')->label('リリースキー'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function pvpRewardTable(string $rewardCategory): array
    {
        $mstPvpRewardGroups = MstPvpRewardGroup::query()
            ->with(['mst_pvp_rewards'])
            ->where('mst_pvp_id', $this->mstPvpId)
            ->where('reward_category', $rewardCategory)
            ->get();

        $rewardIds = $mstPvpRewardGroups->flatMap(function ($group) {
            return $group->mst_pvp_rewards->pluck('id');
        })->unique();
        $rewardDtoList = MstPvpReward::query()
            ->whereIn('id', $rewardIds)
            ->get()
            ->map(function (MstPvpReward $mstPvpRewards) {
                return $mstPvpRewards->reward;
            });
        $rewardInfos = $this->getRewardInfos($rewardDtoList);

        $conditionName = '';
        switch ($rewardCategory) {
            case PvpRewardCategory::RANKING->value :
                $conditionName = '順位';
                break;
            case PvpRewardCategory::RANK_CLASS->value :
                $conditionName = 'ランククラス';
                $mstPvpRanks = MstPvpRank::query()->get()->keyBy('id');
                break;
        }

        $pvpRewardTableRows = [];
        foreach ($mstPvpRewardGroups as $mstPvpRewardGroup) {
            /** @var MstPvpRewardGroup $mstPvpRewardGroup */
            $rewards = [];
            foreach ($mstPvpRewardGroup->mst_pvp_rewards as $mstPvpReward) {
                $rewards[] = $rewardInfos->get($mstPvpReward->id);
            }

            if ($mstPvpRewardGroup->isRankClassReward()) {
                $mstPvpRank = $mstPvpRanks->get($mstPvpRewardGroup->condition_value);
                $pvpRewardTableRows[] = [
                    'ランク' => $mstPvpRank?->rank_class_type ?? '',
                    'ランクレベル' => $mstPvpRank?->rank_class_level ?? '',
                    '報酬情報' => $rewards,
                    'sort' => $mstPvpRank?->required_lower_score ?? 0,
                ];
            } else {
                $pvpRewardTableRows[] = [
                    $conditionName => $mstPvpRewardGroup->condition_value,
                    '報酬情報' => $rewards,
                    'sort' => (int)$mstPvpRewardGroup->condition_value,
                ];
            }
        }

        // ソートを実行し、ソート列は表示しないので削除
        usort($pvpRewardTableRows, function ($a, $b) {
            return $a['sort'] <=> $b['sort'];
        });
        foreach ($pvpRewardTableRows as &$row) {
            unset($row['sort']);
        }

        return $pvpRewardTableRows;
    }
}
