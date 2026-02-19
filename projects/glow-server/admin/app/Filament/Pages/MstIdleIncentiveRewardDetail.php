<?php

namespace App\Filament\Pages;

use App\Constants\RewardType;
use App\Domain\IdleIncentive\Services\IdleIncentiveRewardService;
use App\Domain\IdleIncentive\Services\IdleIncentiveService;
use App\Dtos\RewardDto;
use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Models\Mst\MstIdleIncentive;
use App\Models\Mst\MstIdleIncentiveItem;
use App\Models\Mst\MstIdleIncentiveReward;
use App\Traits\RewardInfoGetTrait;
use App\Utils\StringUtil;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Infolist;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class MstIdleIncentiveRewardDetail extends MstDetailBasePage implements HasTable
{
    use InteractsWithTable;
    use InteractsWithInfolists;
    use RewardInfoGetTrait;

    protected static ?string $title = '探索報酬詳細';
    protected static string $view = 'filament.pages.mst-idle-incentive-reward-detail';

    public string $mstStageId = '';

    protected $queryString = [
        'mstStageId',
        'minutesElapsed'
    ];

    //初回遷移時は10分経過時の報酬情報を設定
    public string $minutesElapsed = '';

    private ?MstIdleIncentive $mstIdleIncentive = null;

    public function mount()
    {
        parent::mount();

        $this->setElapsedMinutes();
    }

    public function setElapsedMinutes()
    {
        $mstIdleIncentive = $this->getMstIdleIncentive();
        if (blank($this->minutesElapsed)) {
            $this->minutesElapsed = $mstIdleIncentive?->reward_increase_interval_minutes ?? 10;
        }

        $idleIncentiveService = app(IdleIncentiveService::class);
        $this->minutesElapsed = (string) $idleIncentiveService->clampIdleMinutes(
            $mstIdleIncentive->toEntity(),
            (int) $this->minutesElapsed,
        );
    }

    protected function getResourceClass(): ?string
    {
        // 一覧ページがない詳細ページのため、Resourceは指定しない
        return null;
    }

    protected function getMstModelByQuery(): ?MstIdleIncentiveReward
    {
        return MstIdleIncentiveReward::query()
            ->where('mst_stage_id', $this->mstStageId)
            ->first();
    }

    protected function getMstNotFoundDangerNotificationBody(): string
    {
        return sprintf('mst_stages.id: %s', $this->mstStageId);
    }

    protected function getSubTitle(): string
    {
        $mstStage = $this->getMstModel()?->mst_stage;
        if (!$mstStage) {
            return '';
        }

        return '「' . StringUtil::makeIdNameViewString(
            $mstStage->id,
            $mstStage?->mst_stage_i18n->name ?? '',
        ) . '」ステージまでクリア済みの報酬';
    }

    protected function getAdditionalBreadcrumbs(): array
    {
        return [
            MstIdleIncentiveDetail::getUrl() => MstIdleIncentiveDetail::getMainTitle(),
        ];
    }

    /**
     * 1つ前のページがResourceではないので、リダイレクト先のURLを指定する
     * @return string
     */
    protected function getRedicrectUrl(): ?string
    {
        return MstIdleIncentiveDetail::getUrl();
    }

    public function table(Table $table): Table
    {
        $query = MstIdleIncentiveReward::query();

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

    public function getMstIdleIncentive()
    {
        if (is_null($this->mstIdleIncentive)) {
            $this->mstIdleIncentive = MstIdleIncentive::query()->first();
        }
        return $this->mstIdleIncentive;
    }

    public function infoList(): Infolist
    {
        $mstIdleIncentiveReward = $this->getMstModel();

        $state = [
            'mst_stage_info' => StringUtil::makeIdNameViewString(
                $mstIdleIncentiveReward->mst_stage->id,
                $mstIdleIncentiveReward->mst_stage?->mst_stage_i18n->name ?? '',
            ),
            'mst_stage_id' => $mstIdleIncentiveReward->mst_stage->id,
        ];

        return $this->makeInfolist()
            ->state($state)
            ->schema([
                Section::make('ステージ情報')
                    ->schema([
                        TextEntry::make('mst_stage_info')
                    ->hiddenLabel()
                    ->url(
                        StageDetail::getUrl(
                            [
                                'stageId' => $state['mst_stage_id'],
                            ]
                        )
                    ),
                    ])
            ]);
    }

    public function getRewardTableRows(): array
    {
        $rows = [];

        $mstIdleIncentiveReward = $this->getMstModel();

        // mst_idle_incentive_rewardsの報酬情報
        $rewardDtos = collect();
        $rewardDtos->push(
            new RewardDto(
                $mstIdleIncentiveReward->id . RewardType::COIN->value,
                RewardType::COIN->value,
                null,
                0,
            )
        );
        $rewardDtos->push(
            new RewardDto(
                $mstIdleIncentiveReward->id . RewardType::EXP->value,
                RewardType::EXP->value,
                null,
                0,
            )
        );
        $rewardInfos = $this->getRewardInfos($rewardDtos)->keyBy->getResourceType();
        $rows[] = [
            '報酬情報' => [$rewardInfos[RewardType::COIN->value]],
            'ベース獲得量' => $mstIdleIncentiveReward->base_coin_amount,
            '獲得量' => $this->calcAmountByMinutes($mstIdleIncentiveReward->base_coin_amount),
        ];
        $rows[] = [
            '報酬情報' => [$rewardInfos[RewardType::EXP->value]],
            'ベース獲得量' => $mstIdleIncentiveReward->base_exp_amount,
            '獲得量' => $this->calcAmountByMinutes($mstIdleIncentiveReward->base_exp_amount),
        ];

        // mst_idle_incentive_itemsの報酬情報
        $mstIdleIncentiveItems = MstIdleIncentiveItem::query()
            ->where('mst_idle_incentive_item_group_id', $mstIdleIncentiveReward->mst_idle_incentive_item_group_id)
            ->get();
        $itemRewardDtos = collect();
        foreach ($mstIdleIncentiveItems as $mstIdleIncentiveItem) {
            $itemRewardDtos->push($mstIdleIncentiveItem->getRewardAttribute(0));
        }
        $itemRewardInfos = $this->getRewardInfos($itemRewardDtos);
        foreach ($mstIdleIncentiveItems as $mstIdleIncentiveItem) {
            $itemRewardInfo = $itemRewardInfos->get($mstIdleIncentiveItem->id);
            if (is_null($itemRewardInfo)) {
                continue;
            }
            $rows[] = [
                '報酬情報' => [$itemRewardInfo],
                'ベース獲得量' => $mstIdleIncentiveItem->base_amount,
                '獲得量' => $this->calcAmountByMinutes($mstIdleIncentiveItem->base_amount),
            ];
        }

        return $rows;
    }

    public function calcAmountByMinutes(float $baseAmount): int
    {
        $mstIdleIncentive = $this->getMstIdleIncentive();

        $idleIncentiveRewardService = app(IdleIncentiveRewardService::class);

        return $idleIncentiveRewardService->calcAmountByMinutes(
            $baseAmount,
            $mstIdleIncentive->reward_increase_interval_minutes,
            $this->minutesElapsed
        );
    }

    public function send(){
        $this->redirectRoute(
            'filament.admin.pages.mst-idle-incentive-reward-detail',
            [
                'mstStageId'     => $this->mstStageId,
                'minutesElapsed' => $this->minutesElapsed,
            ]
        );
    }
}
