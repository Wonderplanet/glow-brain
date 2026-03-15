<?php

namespace App\Filament\Pages;

use App\Constants\MissionTabs;
use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Models\Mst\MstMissionAchievement;
use App\Models\Mst\MstMissionAchievementDependency;
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

class MstMissionAchievementDetail extends MstDetailBasePage implements HasTable
{
    use RewardInfoGetTrait;
    use InteractsWithTable;
    use InteractsWithInfolists;

    protected static string $view = 'filament.pages.mst-mission-achievement-detail';
    protected static ?string $title = 'アチーブメントミッション詳細';

    public string $mstMissionAchievementId = '';

    protected $queryString = [
        'mstMissionAchievementId',
    ];

    protected function getResourceClass(): ?string
    {
        return MstMissionAchievements::class;
    }

    protected function getAdditionalBreadcrumbs(): array
    {
        $mstMissionAchievement = $this->getMstModel();
        if ($mstMissionAchievement === null) {
            return [];
        }

        return [
            MstMissionAchievements::getUrl() => MissionTabs::MISSION_ACHIEVEMENT,
        ];
    }

    protected function getMstModelByQuery(): ?MstMissionAchievement
    {
        return MstMissionAchievement::query()
            ->where('id', $this->mstMissionAchievementId)
            ->first();
    }

    protected function getMstNotFoundDangerNotificationBody(): string
    {
        return sprintf('mst_mission_achievement.id %s', $this->mstMissionAchievementId);
    }

    protected function getSubTitle(): string
    {
        $mstMissionAchievement = $this->getMstModel();
        return StringUtil::makeIdNameViewString(
            $mstMissionAchievement->id,
            $mstMissionAchievement->mst_mission_i18n?->description ?? '',
        );
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(MstMissionAchievement::query())
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
        $mstMissionAchievement = $this->getMstModel();

        $state = [
            'id'                            => $mstMissionAchievement->id,
            'release_key'                   => $mstMissionAchievement->release_key,
            'group_key'                     => $mstMissionAchievement->group_key,
            'mst_mission_reward_group_id'   => $mstMissionAchievement->mst_mission_reward_group_id,
            'sort_order'                    => $mstMissionAchievement->sort_order,
            'description'                   => $mstMissionAchievement->mst_mission_i18n?->description ?? '',
        ];

        $fieldset = Fieldset::make('アチーブメントミッション詳細')
            ->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('release_key')->label('リリースキー'),
                TextEntry::make('group_key')->label('コンプリート用グループ'),
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
        $mstMissionAchievement = $this->getMstModel();
        $state = [
            'criterion_type'    => $mstMissionAchievement->criterion_type,
            'criterion_value'   => $mstMissionAchievement->criterion_value,
            'criterion_count'   => $mstMissionAchievement->criterion_count,
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

    public function unlockCriterionList(): InfoList
    {
        $mstMissionAchievement = $this->getMstModel();
        $state = [
            'unlock_criterion_type'     => $mstMissionAchievement->unlock_criterion_type,
            'unlock_criterion_value'    => $mstMissionAchievement->unlock_criterion_value,
            'unlock_criterion_count'    => $mstMissionAchievement->unlock_criterion_count,
        ];

        $fieldset = Fieldset::make('開放条件情報')
            ->schema([
                TextEntry::make('unlock_criterion_type')->label('開放条件タイプ'),
                TextEntry::make('unlock_criterion_value')->label('開放条件値'),
                TextEntry::make('unlock_criterion_count')->label('開放条件の達成回数'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function destinationSceneList(): InfoList
    {
        $mstMissionAchievement = $this->getMstModel();
        $state = [
            'destination_scene' => $mstMissionAchievement->destination_scene,
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
        $mstMissionAchievement = $this->getMstModel();
        $query = MstMissionReward::query()
            ->where('group_id', $mstMissionAchievement->mst_mission_reward_group_id);

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

    public function mstMissionAchievementDependencyList(): array
    {
        $mstMissionAchievement = $this->getMstModel();
        $mstMissionAchievementDependency = MstMissionAchievementDependency::query()
            ->where('mst_mission_achievement_id', $mstMissionAchievement->id)
            ->get();

        $dependencyData = [];
        foreach ($mstMissionAchievementDependency as $value) {
            $dependencyData[] = [
                'id'            => $value->id,
                'release_key'   => $value->release_key,
                'group_id'      => $value->group_id,
                'unlock_order'  => $value->unlock_order,
            ];
        }

        return $dependencyData;
    }
}
