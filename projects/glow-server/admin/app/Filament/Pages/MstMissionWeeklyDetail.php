<?php

namespace App\Filament\Pages;

use App\Constants\MissionTabs;
use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Models\Mst\MstMissionReward;
use App\Models\Mst\MstMissionWeekly;
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

class MstMissionWeeklyDetail extends MstDetailBasePage implements HasTable
{
    use RewardInfoGetTrait;
    use InteractsWithTable;
    use InteractsWithInfolists;

    protected static string $view = 'filament.pages.mst-mission-weekly-detail';
    protected static ?string $title = 'ウィークリーミッション詳細';

    public string $mstMissionWeeklyId = '';

    protected $queryString = [
        'mstMissionWeeklyId',
    ];

    private ?MstMissionWeekly $MstMissionWeekly;

    protected function getResourceClass(): ?string
    {
        return MstMissionWeeklies::class;
    }

    protected function getAdditionalBreadcrumbs(): array
    {
        $mstMissionWeekly = $this->getMstModel();
        if ($mstMissionWeekly === null) {
            return [];
        }

        return [
            MstMissionWeeklies::getUrl() => MissionTabs::MISSION_WEEKLY,
        ];
    }

    protected function getMstModelByQuery(): ?MstMissionWeekly
    {
        return MstMissionWeekly::query()
            ->where('id', $this->mstMissionWeeklyId)
            ->first();
    }

    protected function getSubTitle(): string
    {
        $mstMissionWeekly = $this->getMstModel();
        return StringUtil::makeIdNameViewString(
            $mstMissionWeekly->id,
            $mstMissionWeekly->mst_mission_i18n?->description ?? '',
        );
    }

    private function setTitle()
    {
        $description = $this->mstMissionWeekly ? ($this->mstMissionWeekly->mst_mission_i18n?->description ?? '') : '';
        $this::$title .= sprintf(' / [%s] %s', $this->mstMissionWeekly->id, $description);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(MstMissionWeekly::query())
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
        $mstMissionWeekly = $this->getMstModel();

        $state = [
            'id'                            => $mstMissionWeekly->id,
            'release_key'                   => $mstMissionWeekly->release_key,
            'group_key'                     => $mstMissionWeekly->group_key,
            'bonus_point'                   => $mstMissionWeekly->bonus_point,
            'mst_mission_reward_group_id'   => $mstMissionWeekly->mst_mission_reward_group_id,
            'description'                   => $mstMissionWeekly->mst_mission_i18n?->description ?? '',
            'sort_order'                    => $mstMissionWeekly->sort_order,
        ];

        $fieldset = Fieldset::make('ウィークリーミッション詳細')
            ->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('release_key')->label('リリースキー'),
                TextEntry::make('group_key')->label('コンプリート用グループ'),
                TextEntry::make('bonus_point')->label('ミッションボーナスポイント'),
                TextEntry::make('mst_mission_reward_group_id')->label('報酬グループID'),
                TextEntry::make('sort_order')->label('表示順'),
                TextEntry::make('description')->label('説明'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function criterionList(): InfoList
    {
        $mstMissionWeekly = $this->getMstModel();

        $state = [
            'criterion_type'    => $mstMissionWeekly->criterion_type,
            'criterion_value'   => $mstMissionWeekly->criterion_value,
            'criterion_count'   => $mstMissionWeekly->criterion_count,
        ];

        $fieldset = Fieldset::make('達成条件情報')
            ->schema([
                TextEntry::make('criterion_type')->label('達成条件タイプ'),
                TextEntry::make('criterion_value')->label('達成条件値'),
                TextEntry::make('criterion_count')->label('達成回数'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function destinationSceneList(): InfoList
    {
        $mstMissionWeekly = $this->getMstModel();

        $state = [
            'destination_scene' => $mstMissionWeekly->destination_scene,
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
        $mstMissionWeekly = $this->getMstModel();

        $query = MstMissionReward::query()
            ->where('group_id', $mstMissionWeekly->mst_mission_reward_group_id);

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
