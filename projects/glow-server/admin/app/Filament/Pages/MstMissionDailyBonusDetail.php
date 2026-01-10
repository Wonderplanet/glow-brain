<?php

namespace App\Filament\Pages;

use App\Constants\MissionDailyBonusType;
use App\Constants\MissionTabs;
use App\Constants\NavigationGroups;
use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Models\Mst\MstMissionDailyBonus;
use App\Models\Mst\MstMissionReward;
use App\Tables\Columns\RewardInfoColumn;
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

class MstMissionDailyBonusDetail extends MstDetailBasePage implements HasTable
{
    use RewardInfoGetTrait;
    use InteractsWithTable;
    use InteractsWithInfolists;

    protected static string $view = 'filament.pages.mst-mission-daily-bonus-detail';

    protected static ?string $title = 'ログインボーナス詳細';

    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_VIEWER->value;

    public string $mstMissionDailyBonusDailyId = '';

    protected $queryString = [
        'mstMissionDailyBonusDailyId',
    ];

    protected function getResourceClass(): ?string
    {
        return MstMissionDailyBonus::class;
    }

    protected function getAdditionalBreadcrumbs(): array
    {
        $mstMissionDaily = $this->getMstModel();
        if ($mstMissionDaily === null) {
            return [];
        }

        return [
            MstMissionDailyBonuses::getUrl() => MissionTabs::MISSION_DAILY_BONUS,
        ];
    }

    protected function getMstModelByQuery(): ?MstMissionDailyBonus
    {
        return MstMissionDailyBonus::query()
            ->where('id', $this->mstMissionDailyBonusDailyId)
            ->first();
    }

    protected function getSubTitle(): string
    {
        $mstMissionDaily = $this->getMstModel();
        return StringUtil::makeIdNameViewString(
            $mstMissionDaily->id,
            ''
        );
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(MstMissionDailyBonus::query())
            ->searchable(false)
            ->columns([
                TextColumn::make('id')
                    ->label('Id')
                    ->searchable()
                    ->sortable(),
            ]);
    }

    public function infoList(): InfoList
    {
        $mstMissionDailyBonus = $this->getMstModel();

        $missionDailyBonusType = MissionDailyBonusType::tryFrom($mstMissionDailyBonus->mission_daily_bonus_type);

        $state = [
            'id'                            => $mstMissionDailyBonus->id,
            'mission_daily_bonus_type'      => $missionDailyBonusType->label(),
            'login_day_count'               => $mstMissionDailyBonus->login_day_count .'日目',
            'mst_mission_reward_group_id'   => $mstMissionDailyBonus->mst_mission_reward_group_id,
            'sort_order'                    => $mstMissionDailyBonus->sort_order,
        ];

        $fieldset = Fieldset::make('ログインボーナス詳細')
            ->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('mission_daily_bonus_type')->label('ログインボーナスタイプ'),
                TextEntry::make('login_day_count')->label('条件とするログイン日数'),
                TextEntry::make('mst_mission_reward_group_id')->label('報酬グループID'),
                TextEntry::make('sort_order')->label('表示順'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function rewardTable(): ?Table
    {
        $mstMissionDailyBonus = $this->getMstModel();

        $query = MstMissionReward::query()
            ->where('group_id', $mstMissionDailyBonus->mst_mission_reward_group_id);

        $rewardDtoList = MstMissionReward::query()->get()->map(function (MstMissionReward $mstMissionReward) {
            return $mstMissionReward->reward;
        });

        $rewardInfos = $this->getRewardInfos($rewardDtoList);

        return $this->getTable()
            ->heading('報酬情報')
            ->query($query)
            ->columns([
                TextColumn::make('resource_type')->label('報酬タイプ'),
                RewardInfoColumn::make('resource_id')
                    ->label('報酬情報')
                    ->getStateUsing(
                        function ($record) use ($rewardInfos){
                            return $rewardInfos->get($record->id);
                        }
                    ),
            ])
            ->paginated(false);
    }
}
