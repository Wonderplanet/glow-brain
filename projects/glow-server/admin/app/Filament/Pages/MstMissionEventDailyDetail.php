<?php

namespace App\Filament\Pages;

use App\Constants\MissionTabs;
use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Models\Mst\MstMissionEventDaily;
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

class MstMissionEventDailyDetail extends MstDetailBasePage implements HasTable
{
    use RewardInfoGetTrait;
    use InteractsWithTable;
    use InteractsWithInfolists;
    protected static string $view = 'filament.pages.mst-mission-event-daily-detail';
    protected static ?string $title = 'イベントデイリーミッション詳細';

    public string $mstMissionEventDailyId = '';

    protected $queryString = [
        'mstMissionEventDailyId',
    ];

    protected function getResourceClass(): ?string
    {
        return MstMissionEventDailies::class;
    }

    protected function getAdditionalBreadcrumbs(): array
    {
        $mstMissionEventDaily = $this->getMstModel();
        if ($mstMissionEventDaily === null) {
            return [];
        }

        return [
            MstMissionEventDailies::getUrl() => MissionTabs::MISSION_EVENT_DAILY,
        ];
    }

    protected function getMstModelByQuery(): ?MstMissionEventDaily
    {
        return MstMissionEventDaily::query()
            ->where('id', $this->mstMissionEventDailyId)
            ->first();
    }

    protected function getMstNotFoundDangerNotificationBody(): string
    {
        return sprintf('mst_mission_event.id %s', $this->mstMissionEventDailyId);
    }

    protected function getSubTitle(): string
    {
        $mstMissionEventDaily = $this->getMstModel();
        return StringUtil::makeIdNameViewString(
            $mstMissionEventDaily->id,
            $mstMissionEventDaily->mst_mission_i18n?->description ?? '',
        );
    }

    public function table(Table $table): Table
    {
        $query = MstMissionEventDaily::query();

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

    public function infoList(): InfoList
    {
        $mstMissionEventDaily = $this->getMstModel();

        $state = [
            'id'                            => $mstMissionEventDaily->id,
            'release_key'                   => $mstMissionEventDaily->release_key,
            'mst_event_id'                  => $mstMissionEventDaily->mst_event_id,
            'group_key'                     => $mstMissionEventDaily->group_key,
            'mst_mission_reward_group_id'   => $mstMissionEventDaily->mst_mission_reward_group_id,
            'sort_order'                    => $mstMissionEventDaily->sort_order,
        ];

        $fieldset = Fieldset::make('イベントデイリーミッション詳細')
            ->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('release_key')->label('リリースキー'),
                TextEntry::make('mst_event_id')->label('イベントID'),
                TextEntry::make('group_key')->label('分類キー'),
                TextEntry::make('mst_mission_reward_group_id')->label('mst_mission_reward_groups.group_id'),
                TextEntry::make('sort_order')->label('並び順'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function criterionList(): InfoList
    {
        $mstMissionEventDaily = $this->getMstModel();

        $state = [
            'criterion_type'    => $mstMissionEventDaily->criterion_type,
            'criterion_value'   => $mstMissionEventDaily->criterion_value,
            'criterion_count'   => $mstMissionEventDaily->criterion_count,
        ];

        $fieldset = Fieldset::make('達成条件情報')
            ->schema([
                TextEntry::make('criterion_type')->label('達成条件タイプ'),
                TextEntry::make('criterion_count')->label('達成回数'),
                TextEntry::make('criterion_value')->label('達成条件値'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function destinationSceneList(): InfoList
    {
        $mstMissionEventDaily = $this->getMstModel();

        $state = [
            'destination_scene' => $mstMissionEventDaily->destination_scene,
        ];

        $fieldset = Fieldset::make('遷移情報')
            ->schema([
                TextEntry::make('destination_scene')->label('ミッションから遷移する画面'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function rewardTable(): ?Table
    {
        $mstMissionEventDaily = $this->getMstModel();

        $query = MstMissionReward::query()
            ->where('group_id', $mstMissionEventDaily->mst_mission_reward_group_id);

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
