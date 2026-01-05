<?php

namespace App\Filament\Pages;

use App\Constants\MissionTabs;
use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Models\Mst\MstMissionDaily;
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

class MstMissionDailyDetail extends MstDetailBasePage implements HasTable
{
    use RewardInfoGetTrait;
    use InteractsWithTable;
    use InteractsWithInfolists;

    protected static string $view = 'filament.pages.mst-mission-daily-detail';
    protected static ?string $title = 'デイリーミッション詳細';
    public string $mstMissionDailyId = '';

    protected $queryString = [
        'mstMissionDailyId',
    ];

    protected function getResourceClass(): ?string
    {
        return MstMissionDailies::class;
    }

    protected function getAdditionalBreadcrumbs(): array
    {
        $mstMissionDaily = $this->getMstModel();
        if ($mstMissionDaily === null) {
            return [];
        }

        return [
            MstMissionDailies::getUrl() => MissionTabs::MISSION_DAILY,
        ];
    }

    protected function getMstModelByQuery(): ?MstMissionDaily
    {
        return MstMissionDaily::query()
            ->where('id', $this->mstMissionDailyId)
            ->first();
    }

    protected function getSubTitle(): string
    {
        $mstMissionDaily = $this->getMstModel();
        return StringUtil::makeIdNameViewString(
            $mstMissionDaily->id,
            $mstMissionDaily->mst_mission_i18n?->description ?? '',
        );
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(MstMissionDaily::query())
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
        $mstMissionDaily = $this->getMstModel();

        $state = [
            'id'                            => $mstMissionDaily->id,
            'release_key'                   => $mstMissionDaily->release_key,
            'group_key'                     => $mstMissionDaily->group_key,
            'bonus_point'                   => $mstMissionDaily->bonus_point,
            'mst_mission_reward_group_id'   => $mstMissionDaily->mst_mission_reward_group_id,
            'destination_scene'             => $mstMissionDaily->destination_scene,
            'sort_order'                    => $mstMissionDaily->sort_order,
            'description'                   => $mstMissionDaily->mst_mission_i18n?->description ?? '',
        ];

        $fieldset = Fieldset::make('デイリーミッション詳細')
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
        $mstMissionDaily = $this->getMstModel();

        $state = [
            'criterion_type'    => $mstMissionDaily->criterion_type,
            'criterion_count'   => $mstMissionDaily->criterion_count,
            'criterion_value'   => $mstMissionDaily->criterion_value,
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
        $mstMissionDaily = $this->getMstModel();

        $state = [
            'destination_scene' => $mstMissionDaily->destination_scene,
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
        $mstMissionDaily = $this->getMstModel();

        $query = MstMissionReward::query()
            ->where('group_id', $mstMissionDaily->mst_mission_reward_group_id);

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
