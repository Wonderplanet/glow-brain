<?php

namespace App\Filament\Pages;

use App\Constants\MissionTabs;
use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Models\Mst\MstMissionBeginner;
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

class MstMissionBeginnerDetail extends MstDetailBasePage implements HasTable
{
    use RewardInfoGetTrait;
    use InteractsWithTable;
    use InteractsWithInfolists;

    protected static string $view = 'filament.pages.mst-mission-beginner-detail';
    protected static ?string $title = '初心者ミッション詳細';
    public string $mstMissionBeginnerId = '';

    protected $queryString = [
        'mstMissionBeginnerId',
    ];

    protected function getResourceClass(): ?string
    {
        return MstMissionDailies::class;
    }

    protected function getAdditionalBreadcrumbs(): array
    {
        $mstMissionBeginner = $this->getMstModel();
        if ($mstMissionBeginner === null) {
            return [];
        }

        return [
            MstMissionBeginners::getUrl() => MissionTabs::MISSION_BEGINNER,
        ];
    }

    protected function getMstModelByQuery(): ?MstMissionBeginner
    {
        return MstMissionBeginner::query()
            ->where('id', $this->mstMissionBeginnerId)
            ->first();
    }

    protected function getSubTitle(): string
    {
        $mstMissionBeginner = $this->getMstModel();
        return StringUtil::makeIdNameViewString(
            $mstMissionBeginner->id,
            $mstMissionBeginner->mst_mission_i18n?->description ?? '',
        );
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(MstMissionBeginner::query())
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
        $mstMissionBeginner = $this->getMstModel();

        $state = [
            'id'                            => $mstMissionBeginner->id,
            'release_key'                   => $mstMissionBeginner->release_key,
            'group_key'                     => $mstMissionBeginner->group_key,
            'bonus_point'                   => $mstMissionBeginner->bonus_point,
            'mst_mission_reward_group_id'   => $mstMissionBeginner->mst_mission_reward_group_id,
            'destination_scene'             => $mstMissionBeginner->destination_scene,
            'sort_order'                    => $mstMissionBeginner->sort_order,
            'description'                   => $mstMissionBeginner->mst_mission_i18n?->description ?? '',
            'unlock_day'                    => $mstMissionBeginner->unlock_day,
        ];

        $fieldset = Fieldset::make('初心者ミッション詳細')
            ->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('release_key')->label('リリースキー'),
                TextEntry::make('group_key')->label('コンプリート用グループ'),
                TextEntry::make('bonus_point')->label('ミッションボーナスポイント'),
                TextEntry::make('mst_mission_reward_group_id')->label('報酬グループID'),
                TextEntry::make('sort_order')->label('表示順'),
                TextEntry::make('description')->label('説明'),
                TextEntry::make('unlock_day')->label('開始からの開放日'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function criterionList(): InfoList
    {
        $mstMissionBeginner = $this->getMstModel();

        $state = [
            'criterion_type'    => $mstMissionBeginner->criterion_type,
            'criterion_count'   => $mstMissionBeginner->criterion_count,
            'criterion_value'   => $mstMissionBeginner->criterion_value,
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
        $mstMissionBeginner = $this->getMstModel();

        $state = [
            'destination_scene' => $mstMissionBeginner->destination_scene,
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
        $mstMissionBeginner = $this->getMstModel();

        $query = MstMissionReward::query()
            ->where('group_id', $mstMissionBeginner->mst_mission_reward_group_id);

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
