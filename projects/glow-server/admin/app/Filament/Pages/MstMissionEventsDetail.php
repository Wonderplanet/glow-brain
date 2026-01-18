<?php

namespace App\Filament\Pages;

use App\Constants\MissionTabs;
use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Models\Mst\MstMissionEvent;
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

class MstMissionEventsDetail extends MstDetailBasePage implements HasTable
{
    use RewardInfoGetTrait;
    use InteractsWithTable;
    use InteractsWithInfolists;

    protected static string $view = 'filament.pages.mst-mission-events-detail';
    protected static ?string $title = 'イベントミッション詳細';

    public string $mstMissionEventId = '';

    protected $queryString = [
        'mstMissionEventId',
    ];

    protected function getResourceClass(): ?string
    {
        return MstMissionEvents::class;
    }

    protected function getAdditionalBreadcrumbs(): array
    {
        $mstMissionEvent = $this->getMstModel();
        if ($mstMissionEvent === null) {
            return [];
        }

        return [
            MstMissionEvents::getUrl() => MissionTabs::MISSION_EVENT,
        ];
    }

    protected function getMstModelByQuery(): ?MstMissionEvent
    {
        return MstMissionEvent::query()
            ->where('id', $this->mstMissionEventId)
            ->first();
    }

    protected function getMstNotFoundDangerNotificationBody(): string
    {
        return sprintf('mst_mission_event.id %s', $this->mstMissionEventId);
    }

    protected function getSubTitle(): string
    {
        $mstMissionEvent = $this->getMstModel();
        return StringUtil::makeIdNameViewString(
            $mstMissionEvent->id,
            $mstMissionEvent->mst_mission_i18n?->description ?? '',
        );
    }

    public function table(Table $table): Table
    {
        $query = MstMissionEvent::query();

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
        $mstMissionEvent = $this->getMstModel();

        $state = [
            'id'                            => $mstMissionEvent->id,
            'release_key'                   => $mstMissionEvent->release_key,
            'mst_event_id'                  => $mstMissionEvent->mst_event_id,
            'unlock_criterion_type'         => $mstMissionEvent->unlock_criterion_type,
            'unlock_criterion_value'        => $mstMissionEvent->unlock_criterion_value,
            'unlock_criterion_count'        => $mstMissionEvent->unlock_criterion_count,
            'group_key'                     => $mstMissionEvent->group_key,
            'mst_mission_reward_group_id'   => $mstMissionEvent->mst_mission_reward_group_id,
            'sort_order'                    => $mstMissionEvent->sort_order,
        ];

        $fieldset = Fieldset::make('イベントミッション詳細')
            ->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('release_key')->label('リリースキー'),
                TextEntry::make('mst_event_id')->label('イベントID'),
                TextEntry::make('unlock_criterion_type')->label('開放条件タイプ'),
                TextEntry::make('unlock_criterion_value')->label('開放条件値'),
                TextEntry::make('unlock_criterion_count')->label('達成回数'),
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
        $mstMissionEvent = $this->getMstModel();

        $state = [
            'criterion_type'    => $mstMissionEvent->criterion_type,
            'criterion_value'   => $mstMissionEvent->criterion_value,
            'criterion_count'   => $mstMissionEvent->criterion_count,
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
        $mstMissionEvent = $this->getMstModel();

        $state = [
            'destination_scene' => $mstMissionEvent->destination_scene,
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
        $mstMissionEvent = $this->getMstModel();

        $query = MstMissionReward::query()
            ->where('group_id', $mstMissionEvent->mst_mission_reward_group_id);

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
