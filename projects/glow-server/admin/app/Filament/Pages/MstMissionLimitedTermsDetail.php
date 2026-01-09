<?php

namespace App\Filament\Pages;

use App\Constants\MissionTabs;
use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Models\Mst\MstMissionLimitedTerm;
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

class MstMissionLimitedTermsDetail extends MstDetailBasePage implements HasTable
{
    use RewardInfoGetTrait;
    use InteractsWithTable;
    use InteractsWithInfolists;

    protected static string $view = 'filament.pages.mst-mission-limited-terms-detail';
    protected static ?string $title = MissionTabs::MISSION_LIMITED_TERM->value . '詳細';

    public string $mstMissionLimitedTermId = '';

    protected $queryString = [
        'mstMissionLimitedTermId',
    ];

    protected function getResourceClass(): ?string
    {
        return MstMissionLimitedTerms::class;
    }

    protected function getAdditionalBreadcrumbs(): array
    {
        $MstMissionLimitedTerm = $this->getMstModel();
        if ($MstMissionLimitedTerm === null) {
            return [];
        }

        return [
            MstMissionLimitedTerms::getUrl() => MissionTabs::MISSION_LIMITED_TERM,
        ];
    }

    protected function getMstModelByQuery(): ?MstMissionLimitedTerm
    {
        return MstMissionLimitedTerm::query()
            ->where('id', $this->mstMissionLimitedTermId)
            ->first();
    }

    protected function getMstNotFoundDangerNotificationBody(): string
    {
        return sprintf('mst_mission_limited_terms.id %s', $this->mstMissionLimitedTermId);
    }

    protected function getSubTitle(): string
    {
        $MstMissionLimitedTerm = $this->getMstModel();
        return StringUtil::makeIdNameViewString(
            $MstMissionLimitedTerm->id,
            $MstMissionLimitedTerm->mst_mission_i18n?->description ?? '',
        );
    }

    public function table(Table $table): Table
    {
        $query = MstMissionLimitedTerm::query();

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
        $MstMissionLimitedTerm = $this->getMstModel();

        $state = [
            'id'                            => $MstMissionLimitedTerm->id,
            'release_key'                   => $MstMissionLimitedTerm->release_key,
            'progress_group_key'            => $MstMissionLimitedTerm->progress_group_key,
            'mst_mission_reward_group_id'   => $MstMissionLimitedTerm->mst_mission_reward_group_id,
            'sort_order'                    => $MstMissionLimitedTerm->sort_order,
        ];

        $fieldset = Fieldset::make('期間限定ミッション詳細')
            ->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('release_key')->label('リリースキー'),
                TextEntry::make('progress_group_key')->label('分類キー'),
                TextEntry::make('mst_mission_reward_group_id')->label('mst_mission_reward_groups.group_id'),
                TextEntry::make('sort_order')->label('並び順'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function criterionList(): InfoList
    {
        $MstMissionLimitedTerm = $this->getMstModel();

        $state = [
            'criterion_type'    => $MstMissionLimitedTerm->criterion_type,
            'criterion_value'   => $MstMissionLimitedTerm->criterion_value,
            'criterion_count'   => $MstMissionLimitedTerm->criterion_count,
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
        $MstMissionLimitedTerm = $this->getMstModel();

        $state = [
            'destination_scene' => $MstMissionLimitedTerm->destination_scene,
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
        $MstMissionLimitedTerm = $this->getMstModel();

        $query = MstMissionReward::query()
            ->where('group_id', $MstMissionLimitedTerm->mst_mission_reward_group_id);

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
