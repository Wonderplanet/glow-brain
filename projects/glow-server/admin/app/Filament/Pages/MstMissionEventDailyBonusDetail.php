<?php

namespace App\Filament\Pages;

use App\Constants\MissionTabs;
use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Infolists\Components\MstEventInfolistEntry;
use App\Models\Mst\MstMissionEventDailyBonus;
use App\Models\Mst\MstMissionEventDailyBonusSchedule;
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

class MstMissionEventDailyBonusDetail extends MstDetailBasePage implements HasTable
{
    use RewardInfoGetTrait;
    use InteractsWithTable;
    use InteractsWithInfolists;

    protected static string $view = 'filament.pages.mst-mission-event-daily-bonus-detail';

    protected static ?string $title = 'イベントログインボーナス詳細';
    public string $mstMissionEventDailyBonusDailyId = '';

    protected $queryString = [
        'mstMissionEventDailyBonusDailyId',
    ];

    private ?MstMissionEventDailyBonus $mstMissionEventDailyBonus;

    protected function getResourceClass(): ?string
    {
        return MstMissionEventDailyBonus::class;
    }

    protected function getAdditionalBreadcrumbs(): array
    {
        $mstMissionDaily = $this->getMstModel();
        if ($mstMissionDaily === null) {
            return [];
        }

        return [
            MstMissionEventDailyBonuses::getUrl() => MissionTabs::MISSION_EVENT_DAILY_BONUS,
        ];
    }

    protected function getMstModelByQuery(): ?MstMissionEventDailyBonus
    {
        return MstMissionEventDailyBonus::query()
            ->where('id', $this->mstMissionEventDailyBonusDailyId)
            ->first();
    }

    protected function getSubTitle(): string
    {
        $mstMissionDaily = $this->getMstModel();
        return StringUtil::makeIdNameViewString(
            $mstMissionDaily->id,
            $mstMissionDaily->mst_mission_Daily_i18n->description ?? '',
        );
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(MstMissionEventDailyBonus::query())
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
        $mstMissionEventDailyBonus = $this->getMstModel();

        $state = [
            'id'                                        => $mstMissionEventDailyBonus->id,
            'mst_mission_event_daily_bonus_schedule_id' => $mstMissionEventDailyBonus->mst_mission_event_daily_bonus_schedule_id,
            'login_day_count'                           => $mstMissionEventDailyBonus->login_day_count .'日目',
            'mst_mission_reward_group_id'               => $mstMissionEventDailyBonus->mst_mission_reward_group_id,
            'sort_order'                                => $mstMissionEventDailyBonus->sort_order,
            'release_key'                               => $mstMissionEventDailyBonus->release_key,
        ];

        $fieldset = Fieldset::make('イベントログインボーナス詳細')
            ->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('mst_mission_event_daily_bonus_schedule_id')->label('デイリーボーナススケジュールID'),
                TextEntry::make('login_day_count')->label('条件とするログイン日数'),
                TextEntry::make('mst_mission_reward_group_id')->label('報酬グループID'),
                TextEntry::make('sort_order')->label('表示順'),
                TextEntry::make('release_key')->label('リリースキー'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function eventList(): InfoList
    {
        $mstMissionEventDailyBonus = $this->getMstModel();

        $mstMissionEventDailyBonusSchedule = MstMissionEventDailyBonusSchedule::query()
            ->where('id', $mstMissionEventDailyBonus->mst_mission_event_daily_bonus_schedule_id)
            ->first();

        $state = [
            'start_at' => $mstMissionEventDailyBonusSchedule->start_at,
            'end_at' => $mstMissionEventDailyBonusSchedule->end_at,
            'event_info' => $mstMissionEventDailyBonusSchedule->mst_event,
        ];

        $fieldset = Fieldset::make('イベント期間・情報')
            ->schema([
                TextEntry::make('start_at')->label('イベント開始日'),
                TextEntry::make('end_at')->label('イベント終了日'),
                MstEventInfolistEntry::make('event_info')->label('イベント情報'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function rewardTable(): ?Table
    {
        $mstMissionEventDailyBonus = $this->getMstModel();

        $query = MstMissionReward::query()
            ->where('group_id', $mstMissionEventDailyBonus->mst_mission_reward_group_id);

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
