<?php

namespace App\Filament\Pages;

use App\Constants\GachaUpperType;
use App\Constants\RarityType;
use App\Domain\Gacha\Constants\GachaConstants;
use App\Domain\Gacha\Services\GachaService;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUnitFragmentConvert;
use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Filament\Resources\OprGachaResource;
use App\Models\Mst\OprGacha;
use App\Models\Mst\OprGachaDisplayUnitI18n;
use App\Models\Mst\OprGachaPrize;
use App\Models\Mst\OprStepupGacha;
use App\Models\Mst\OprStepupGachaStep;
use App\Models\Mst\OprStepupGachaStepReward;
use App\Entities\RewardInfo;
use App\Services\ConfigGetService;
use App\Traits\RewardInfoGetTrait;
use App\Utils\MathUtil;
use App\Utils\StringUtil;

class OprGachaDetail extends MstDetailBasePage
{
    use RewardInfoGetTrait;

    protected static string $view = 'filament.pages.opr-gacha-detail';

    protected static ?string $title = 'ガシャ詳細';

    public string $oprGachaId = '';

    protected $queryString = [
        'oprGachaId',
    ];

    protected function getResourceClass(): ?string
    {
        return OprGachaResource::class;
    }

    protected function getMstModelByQuery(): ?OprGacha
    {
        return OprGacha::query()->where('id',$this->oprGachaId)?->first();
    }

    protected function getMstNotFoundDangerNotificationBody(): string
    {
        return sprintf('ガシャID: %s', $this->oprGachaId);
    }

    protected function getSubTitle(): string
    {
        return StringUtil::makeIdNameViewString(
            $this->oprGachaId,
            $this->getMstModel()->getName(),
        );
    }

    private function getBasicInfo(): array
    {
        $oprGacha = $this->getMstModel();
        $description = $oprGacha->opr_gacha_i18n?->description ?? '';

        $info = [
            'ガシャID' => $oprGacha->id,
            'ガシャ名' => $oprGacha->opr_gacha_i18n?->name ?? '',
            'ガシャタイプ' => $oprGacha->gacha_type_label,
            'ガシャ訴求文言' => $description,
            '開始日時' => $oprGacha->start_at,
            '終了日時' => $oprGacha->end_at,
            '表示順' => $oprGacha->gacha_priority,
            '表示条件' => $oprGacha->appearance_condition_label,
        ];

        if (!$this->isStepupGacha()) {
            $oprGachaEntity = $oprGacha->toEntity();
            $info['N連数'] = $oprGacha->multi_draw_count;
            $info['確定枠数'] = $oprGachaEntity->hasMultiFixedPrize() ? $oprGacha->multi_fixed_prize_count : 'なし';
        }

        return $info;
    }

    private function getAssetInfo(): array
    {
        $oprGacha = $this->getMstModel();

        $bannerUrlDomain = app(ConfigGetService::class)->getS3BannerUrl() ?? '';
        $banner = $oprGacha->makeBannerUrl($bannerUrlDomain);

        return [
            'バナー' => $banner ?: $oprGacha->opr_gacha_i18n?->banner_url ?? '',
            'バナーID' => $oprGacha->opr_gacha_i18n?->banner_url ?? '',
            'バナーサイズ' => $oprGacha->opr_gacha_i18n?->gacha_banner_size ?? '',
        ];
    }

    private function getUseResourceTableRows(): array
    {
        $useResourceTableRows = [];

        $oprGacha = $this->getMstModel();
        $oprGachaUseResources = $oprGacha->opr_gacha_use_resources
            ->sortBy('cost_priority');

        $costResources = $oprGachaUseResources->map(function ($oprGachaUseResource) {
            return $oprGachaUseResource->cost_resource;
        });
        $costResourceInfos = $this->getRewardInfos($costResources);

        foreach ($oprGachaUseResources as $oprGachaUseResource) {
            $costResourceInfo = $costResourceInfos->get($oprGachaUseResource->id);
            if (is_null($costResourceInfo)) {
                continue;
            }
            $useResourceTableRows[] = [
                'コスト情報' => [$costResourceInfo],
                '引ける回数' => $oprGachaUseResource->draw_count,
                '優先度' => $oprGachaUseResource->cost_priority,
            ];
        }

        return $useResourceTableRows;
    }

    private function getDrawByNormalInfos(): array
    {
        $oprGacha = $this->getMstModel();
        return [
            '通算上限数' => $oprGacha->total_play_limit_count ?? '無制限',
            '1日上限数' => $oprGacha->daily_play_limit_count ?? '無制限',
        ];
    }

    private function getDrawByAdInfos(): array
    {
        $oprGacha = $this->getMstModel();
        return [
            '通算上限数' => $oprGacha->total_ad_limit_count ?? '無制限',
            '1日上限数' => $oprGacha->daily_ad_limit_count ?? '無制限',
            'インターバル(分)' => $oprGacha->ad_play_interval_time ?? '未設定',
        ];
    }

    private function getGachaProbabilityInfos(): array
    {
        if ($this->isStepupGacha()) {
            return [];
        }

        $rarityOrder = RarityType::order();
        $upperLabels = GachaUpperType::labels();

        $oprGacha = $this->getMstModel();
        $gachaProbabilityData = app(GachaService::class)->generateGachaProbability($oprGacha->id);
        $formatToResponse = $gachaProbabilityData->formatToResponse();

        // 枠の指定
        $probabilityGroup = [
            '通常枠' => $formatToResponse['rarityProbabilities'] ?? [],
            '確定枠' => $formatToResponse['fixedProbabilities']['rarityProbabilities'] ?? [],
        ];
        $upperProbabilities = $formatToResponse['upperProbabilities'] ?? [];
        foreach ($upperProbabilities as $upperProbability) {
            $upperType = $upperProbability['upperType'] ?? '';
            $label = $upperLabels->get($upperType, '不明な天井枠');
            $probabilityGroup[$label] = $upperProbability['rarityProbabilities'] ?? [];
        }

        $result = [];
        foreach($probabilityGroup as $label => $rarityProbabilities) {
            if (empty($rarityProbabilities)) {
                continue;
            }

            $temp = ['枠名' => $label];
            $rarityProbabilities = collect($rarityProbabilities)->keyBy('rarity');
            foreach ($rarityOrder as $rarity) {
                $rarityProbability = $rarityProbabilities->get($rarity);
                $probability = $rarityProbability['probability'] ?? 0;
                $temp[$rarity] = $probability . '%';
            }

            $result[] = $temp;
        }

        return $result;
    }

    private function getUpperTableRows(): array
    {
        $upperTableRows = [];

        $oprGacha = $this->getMstModel();
        $oprGachaUppers = $oprGacha->opr_gacha_uppers;
        $oprGachaI18n = $oprGacha?->opr_gacha_i18n;

        foreach ($oprGachaUppers as $oprGachaUpper) {
            $row = [
                '天井グループ' => $oprGachaUpper->upper_group,
                '天井タイプ' => $oprGachaUpper->upper_type_label,
                '天井保証回数' => $oprGachaUpper->count,
                '天井文言' => '',
            ];

            if (!is_null($oprGachaI18n)) {
                $row['天井文言'] = $oprGachaI18n->getUpperDescription($oprGachaUpper->getUpperType());
            }

            $upperTableRows[] = $row;
        }

        return $upperTableRows;
    }

    private function getPrizeTableRows(): array
    {
        if ($this->isStepupGacha()) {
            // ステップアップガシャは、別途ステップごとに抽選テーブル表示を実施するので、ここでは空を返す
            return [];
        }

        $prizeTableRows = [];

        $oprGacha = $this->getMstModel();

        $oprGachaPrizes = OprGachaPrize::query()
            ->where('group_id', $oprGacha->prize_group_id)
            ->get();

        $prizeResources = $oprGachaPrizes->map(function ($oprGachaPrize) {
            return $oprGachaPrize->prize_resource;
        });
        $prizeResources = $this->getRewardInfos($prizeResources);

        $totalWeight = $oprGachaPrizes->sum('weight');
        $oprGachaPrizes = $oprGachaPrizes->sortByDesc('weight');

        foreach ($oprGachaPrizes as $oprGachaPrize) {
            $prizeResource = $prizeResources->get($oprGachaPrize->id);
            if (is_null($prizeResource)) {
                continue;
            }

            $prizeTableRows[] = [
                '排出物情報' => [$prizeResource],
                '出現比重' => $oprGachaPrize->weight,
                '出現確率 (%)' => $this->calcProbabilityPercent($oprGachaPrize->weight, $totalWeight),
                'ピックアップ対象' => $oprGachaPrize->pickup,
            ];
        }

        return $prizeTableRows;
    }

    private function getFixedPrizeTableRows(): array
    {
        $fixedPrizeTableRows = [];

        $oprGacha = $this->getMstModel();
        $oprGachaEntity = $oprGacha->toEntity();

        if (!$oprGachaEntity->hasMultiFixedPrize()) {
            return [];
        }

        $oprGachaPrizes = OprGachaPrize::query()
            ->where('group_id', $oprGacha->fixed_prize_group_id)
            ->get();

        $prizeResources = $oprGachaPrizes->map(function ($oprGachaPrize) {
            return $oprGachaPrize->prize_resource;
        });
        $prizeResources = $this->getRewardInfos($prizeResources);

        $totalWeight = $oprGachaPrizes->sum('weight');
        $oprGachaPrizes = $oprGachaPrizes->sortByDesc('weight');

        foreach ($oprGachaPrizes as $oprGachaPrize) {
            $prizeResource = $prizeResources->get($oprGachaPrize->id);
            if (is_null($prizeResource)) {
                continue;
            }

            $fixedPrizeTableRows[] = [
                '排出物情報' => [$prizeResource],
                '出現比重' => $oprGachaPrize->weight,
                '出現確率 (%)' => $this->calcProbabilityPercent($oprGachaPrize->weight, $totalWeight),
                'ピックアップ対象' => $oprGachaPrize->pickup,
            ];
        }

        return $fixedPrizeTableRows;
    }

    private function getUpperMaxRarityPrizeTableRows(): array
    {
        if ($this->isStepupGacha()) {
            // ステップアップガシャに天井はない
            return [];
        }

        $upperPrizeTableRows = [];

        $oprGacha = $this->getMstModel();
        $oprGachaUppers = $oprGacha->opr_gacha_uppers;

        $isMaxRarityUpper = $oprGachaUppers->contains(function ($oprGachaUpper) {
            return $oprGachaUpper->upper_type->value === GachaUpperType::MAX_RARITY->value;
        });
        if (!$isMaxRarityUpper) {
            return [];
        }

        $oprGachaPrizes = OprGachaPrize::query()
            ->where('group_id', $oprGacha->prize_group_id)
            ->get();

        $mstUnitIds = $oprGachaPrizes->map(function ($oprGachaPrize) {
            return $oprGachaPrize->resource_id;
        })->filter()->unique();

        $upperRarityMstUnits = MstUnit::query()
            ->whereIn('id', $mstUnitIds)
            ->where('rarity', GachaConstants::MAX_RARITY->value) // 最高レアリティのユニットのみを対象
            ->get()
            ->keyBy('id');

        // 最高レアリティのユニットのみを抽出
        $oprGachaPrizes = $oprGachaPrizes->filter(function ($oprGachaPrize) use ($upperRarityMstUnits) {
            $resourceId = $oprGachaPrize->resource_id;
            return $upperRarityMstUnits->has($resourceId);
        });

        $prizeResources = $oprGachaPrizes->map(function ($oprGachaPrize) {
            return $oprGachaPrize->prize_resource;
        });
        $prizeResources = $this->getRewardInfos($prizeResources);

        $totalWeight = $oprGachaPrizes->sum('weight');
        $oprGachaPrizes = $oprGachaPrizes->sortByDesc('weight');

        foreach ($oprGachaPrizes as $oprGachaPrize) {
            $prizeResource = $prizeResources->get($oprGachaPrize->id);
            if (is_null($prizeResource)) {
                continue;
            }

            $upperPrizeTableRows[] = [
                '排出物情報' => [$prizeResource],
                '出現比重' => $oprGachaPrize->weight,
                '出現確率 (%)' => $this->calcProbabilityPercent($oprGachaPrize->weight, $totalWeight),
                'ピックアップ対象' => $oprGachaPrize->pickup,
            ];
        }

        return $upperPrizeTableRows;
    }

    private function getUpperPickupPrizeTableRows(): array
    {
        if ($this->isStepupGacha()) {
            // ステップアップガシャに天井はない
            return [];
        }

        $upperPrizeTableRows = [];

        $oprGacha = $this->getMstModel();
        $oprGachaUppers = $oprGacha->opr_gacha_uppers;

        $isPickupUpper = $oprGachaUppers->contains(function ($oprGachaUpper) {
            return $oprGachaUpper->upper_type->value == GachaUpperType::PICKUP->value;
        });
        if (!$isPickupUpper) {
            return [];
        }

        $oprGachaPrizes = OprGachaPrize::query()
            ->with('mst_unit')
            ->where('group_id', $oprGacha->prize_group_id)
            ->where('pickup', true)
            ->get()
            ->filter(function ($oprGachaPrize) {
                if (!$oprGachaPrize->isUnit()) {
                    // アイテムの場合はそのまま
                    return true;
                }
                // キャラの場合はピックアップかつ最高レアリティを対象とする
                return $oprGachaPrize->isUnit() && !is_null($oprGachaPrize->mst_unit)
                    && $oprGachaPrize->mst_unit->rarity === GachaConstants::MAX_RARITY->value;
            });

        $prizeResources = $oprGachaPrizes->map(function ($oprGachaPrize) {
            return $oprGachaPrize->prize_resource;
        });
        $prizeResources = $this->getRewardInfos($prizeResources);

        $totalWeight = $oprGachaPrizes->sum('weight');
        $oprGachaPrizes = $oprGachaPrizes->sortByDesc('weight');

        foreach ($oprGachaPrizes as $oprGachaPrize) {
            $prizeResource = $prizeResources->get($oprGachaPrize->id);
            if (is_null($prizeResource)) {
                continue;
            }

            $upperPrizeTableRows[] = [
                '排出物情報' => [$prizeResource],
                '出現比重' => $oprGachaPrize->weight,
                '出現確率 (%)' => $this->calcProbabilityPercent($oprGachaPrize->weight, $totalWeight),
                'ピックアップ対象' => $oprGachaPrize->pickup,
            ];
        }

        return $upperPrizeTableRows;
    }

    private function getUnitFragmentConvertTableRows(): array
    {
        $oprGacha = $this->getMstModel();

        if ($this->isStepupGacha()) {
            $oprStepupGachaSteps = OprStepupGachaStep::query()
                ->where('opr_gacha_id', $oprGacha->id)
                ->get();
            $oprGachaEntity = $oprGacha->toEntity();

            $prizeGroupIds = collect();
            foreach ($oprStepupGachaSteps as $oprStepupGachaStep) {
                $prizeGroupId = $oprStepupGachaStep->useCommonPrizeGroupId()
                    ? $oprGacha->prize_group_id
                    : $oprStepupGachaStep->prize_group_id;
                if ($prizeGroupId) {
                    $prizeGroupIds->push($prizeGroupId);
                }

                $fixedPrizeGroupId = $oprStepupGachaStep->hasFixedPrizeGroup()
                    ? $oprStepupGachaStep->fixed_prize_group_id
                    : ($oprGachaEntity->hasFixedPrizeGroup() ? $oprGachaEntity->getFixedPrizeGroupId() : null);
                if ($fixedPrizeGroupId) {
                    $prizeGroupIds->push($fixedPrizeGroupId);
                }
            }

            $oprGachaPrizes = OprGachaPrize::query()
                ->whereIn('group_id', $prizeGroupIds->unique()->filter()->values())
                ->get();
        } else {
            $oprGachaPrizes = OprGachaPrize::query()
                ->where('group_id', $oprGacha->prize_group_id)
                ->orWhere('group_id', $oprGacha->fixed_prize_group_id)
                ->get();
        }

        $mstUnitIds = collect();
        foreach ($oprGachaPrizes as $oprGachaPrize) {
            if (!$oprGachaPrize->isUnit()) {
                continue;
            }
            $mstUnitIds->push($oprGachaPrize->resource_id);
        }

        $unitLabels = MstUnit::query()
            ->select('unit_label')
            ->whereIn('id', $mstUnitIds)
            ->pluck('unit_label', 'unit_label')
            ->unique();

        $mstUnitFragmentConverts = MstUnitFragmentConvert::query()
            ->whereIn('unit_label', $unitLabels)
            ->get();

        $tableRows = [];
        foreach ($mstUnitFragmentConverts as $mstUnitFragmentConvert) {
            $tableRows[] = [
                'ユニットラベル' => $mstUnitFragmentConvert->unit_label,
                '変換個数' => $mstUnitFragmentConvert->convert_amount,
            ];
        }

        return $tableRows;
    }

    private function getDisplayUnitTableRows(): array
    {
        $oprGacha = $this->getMstModel();

        $oprGachaDisplayUnitI18ns = $oprGacha->opr_gacha_display_unit_i18ns
            ->sortBy('sort_order');

        $rewardInfos = $this->getRewardInfos(
            $oprGachaDisplayUnitI18ns->map(
                function (OprGachaDisplayUnitI18n $oprGachaDisplayUnitI18n) {
                    return $oprGachaDisplayUnitI18n->reward;
                }
            )
        );

        $displayUnitTableRows = [];
        foreach ($oprGachaDisplayUnitI18ns as $oprGachaDisplayUnitI18n) {
            $rewardInfo = $rewardInfos->get($oprGachaDisplayUnitI18n->id);
            if (is_null($rewardInfo)) {
                continue;
            }

            $displayUnitTableRows[] = [
                'キャラ' => [$rewardInfo],
                '説明' => $oprGachaDisplayUnitI18n->description,
                '表示順' => $oprGachaDisplayUnitI18n->sort_order,
            ];
        }

        return $displayUnitTableRows;
    }


    private function getAdditionalDescriptions(): array
    {
        $oprGacha = $this->getMstModel();
        $i18n = $oprGacha->opr_gacha_i18n;

        $descriptions = [
            'ガシャ訴求文言' => $i18n?->description ?? '',
        ];

        if (!$this->isStepupGacha()) {
            $descriptions['最高レアリティ天井文言'] = $i18n?->max_rarity_upper_description ?? '';
            $descriptions['ピックアップ天井文言'] = $i18n?->pickup_upper_description ?? '';
        }

        $descriptions['確定枠文言'] = $i18n?->fixed_prize_description ?? '';

        return $descriptions;
    }

    public function isStepupGacha(): bool
    {
        return $this->getMstModel()->toEntity()->isStepup();
    }

    private function getStepupGachaBasicInfo(): array
    {
        $oprStepupGacha = OprStepupGacha::query()
            ->where('opr_gacha_id', $this->getMstModel()->id)
            ->first();

        if (is_null($oprStepupGacha)) {
            return [];
        }

        return [
            'ステップアップガシャID' => $oprStepupGacha->id,
            '最大ステップ数' => $oprStepupGacha->max_step_number,
            '最大周回数' => $oprStepupGacha->max_loop_count ?? '無制限',
        ];
    }

    private function getStepupGachaStepTableRows(): array
    {
        $oprStepupGachaSteps = OprStepupGachaStep::query()
            ->where('opr_gacha_id', $this->getMstModel()->id)
            ->orderBy('step_number')
            ->get();

        if ($oprStepupGachaSteps->isEmpty()) {
            return [];
        }

        $costResources = $oprStepupGachaSteps->map(fn ($oprStepupGachaStep) => $oprStepupGachaStep->cost_resource);
        $costResourceInfos = $this->getRewardInfos($costResources);

        $tableRows = [];
        foreach ($oprStepupGachaSteps as $oprStepupGachaStep) {
            $costResourceInfo = $costResourceInfos->get($oprStepupGachaStep->id);
            $tableRows[] = [
                'ステップ番号' => $oprStepupGachaStep->step_number,
                'コスト情報' => $costResourceInfo ? [$costResourceInfo] : [],
                '抽選数' => $oprStepupGachaStep->draw_count,
                '確定枠数' => $oprStepupGachaStep->fixed_prize_count,
                '確定枠レアリティ閾値' => $oprStepupGachaStep->fixed_prize_rarity_threshold_type?->value ?? 'なし',
                '初回無料' => $oprStepupGachaStep->is_first_free ? 'あり' : 'なし',
            ];
        }

        return $tableRows;
    }

    private function getStepupGachaStepRewardTableRows(): array
    {
        $oprStepupGachaStepRewards = OprStepupGachaStepReward::query()
            ->where('opr_gacha_id', $this->getMstModel()->id)
            ->orderBy('step_number')
            ->orderBy('loop_count_target')
            ->get();

        if ($oprStepupGachaStepRewards->isEmpty()) {
            return [];
        }

        $rewardDtos = $oprStepupGachaStepRewards->map(fn ($oprStepupGachaStepReward) => $oprStepupGachaStepReward->reward);
        $rewardInfos = $this->getRewardInfos($rewardDtos);

        $tableRows = [];
        foreach ($oprStepupGachaStepRewards as $oprStepupGachaStepReward) {
            $rewardInfo = $rewardInfos->get((string) $oprStepupGachaStepReward->id);
            if (is_null($rewardInfo)) {
                continue;
            }
            $stepNumber = $oprStepupGachaStepReward->step_number;
            $loopCountTarget = $oprStepupGachaStepReward->loop_count_target;
            $loopLabel = $loopCountTarget !== null ? $loopCountTarget . '周回目' : '全周回';
            $loopKey = $loopCountTarget ?? 'all';

            if (!isset($tableRows[$stepNumber][$loopKey])) {
                $tableRows[$stepNumber][$loopKey] = [
                    'loop_count_label' => $loopLabel,
                    'rows' => [],
                ];
            }
            $tableRows[$stepNumber][$loopKey]['rows'][] = [
                '報酬情報' => [$rewardInfo],
            ];
        }

        return $tableRows;
    }

    private function getStepupGachaPrizeTableRows(): array
    {
        $oprGacha = $this->getMstModel();
        $oprStepupGachaSteps = OprStepupGachaStep::query()
            ->where('opr_gacha_id', $oprGacha->id)
            ->orderBy('step_number')
            ->get();

        if ($oprStepupGachaSteps->isEmpty()) {
            return [];
        }

        // 各ステップのeffective prize_group_id をマッピング
        $stepPrizeGroupMap = $oprStepupGachaSteps->mapWithKeys(function ($oprStepupGachaStep) use ($oprGacha) {
            $prizeGroupId = $oprStepupGachaStep->useCommonPrizeGroupId()
                ? $oprGacha->prize_group_id
                : $oprStepupGachaStep->prize_group_id;
            return [$oprStepupGachaStep->step_number => $prizeGroupId];
        });

        // 全賞品を一括取得（N+1回避）
        $oprGachaPrizes = OprGachaPrize::query()
            ->whereIn('group_id', $stepPrizeGroupMap->unique()->values()->filter())
            ->get();

        if ($oprGachaPrizes->isEmpty()) {
            return [];
        }

        $prizesByGroup = $oprGachaPrizes->groupBy('group_id');
        /** @var \Illuminate\Support\Collection<int, \App\Dtos\RewardDto> $prizeResources */
        $prizeResources = $oprGachaPrizes->map(fn ($prize) => $prize->prize_resource);
        $prizeInfos = $this->getRewardInfos($prizeResources);

        $tableRows = [];
        foreach ($oprStepupGachaSteps as $oprStepupGachaStep) {
            $prizeGroupId = $stepPrizeGroupMap->get($oprStepupGachaStep->step_number);
            $prizes = $prizesByGroup->get($prizeGroupId, collect());
            $totalWeight = $prizes->sum('weight');
            if ($totalWeight === 0) {
                continue;
            }
            $stepRows = [];
            foreach ($prizes as $prize) {
                $prizeInfo = $prizeInfos->get($prize->id);
                if (is_null($prizeInfo)) {
                    continue;
                }
                $probability = $this->calcProbabilityPercent($prize->weight, $totalWeight);
                $stepRows[] = [
                    '報酬情報' => [$prizeInfo],
                    '重み' => $prize->weight,
                    '確率' => "{$probability}%",
                    'pickup' => $prize->pickup ? 'あり' : 'なし',
                ];
            }
            $tableRows[$oprStepupGachaStep->step_number] = [
                'prize_group_id' => $prizeGroupId,
                'rows' => $stepRows,
            ];
        }

        return $tableRows;
    }

    private function getStepupGachaFixedPrizeTableRows(): array
    {
        $oprGacha = $this->getMstModel();
        $oprStepupGachaSteps = OprStepupGachaStep::query()
            ->where('opr_gacha_id', $oprGacha->id)
            ->where('fixed_prize_count', '>', 0)
            ->orderBy('step_number')
            ->get();

        if ($oprStepupGachaSteps->isEmpty()) {
            return [];
        }

        $oprGachaEntity = $oprGacha->toEntity();

        // 各ステップのeffective fixed_prize_group_id をマッピング
        $stepFixedPrizeGroupMap = $oprStepupGachaSteps->mapWithKeys(function ($oprStepupGachaStep) use ($oprGachaEntity) {
            $fixedPrizeGroupId = $oprStepupGachaStep->hasFixedPrizeGroup()
                ? $oprStepupGachaStep->fixed_prize_group_id
                : ($oprGachaEntity->hasFixedPrizeGroup() ? $oprGachaEntity->getFixedPrizeGroupId() : null);
            return [$oprStepupGachaStep->step_number => $fixedPrizeGroupId];
        });

        $validGroupIds = $stepFixedPrizeGroupMap->filter()->unique()->values();
        if ($validGroupIds->isEmpty()) {
            return [];
        }

        // 全確定枠賞品を一括取得（N+1回避）
        $oprGachaPrizes = OprGachaPrize::query()
            ->whereIn('group_id', $validGroupIds)
            ->get();

        if ($oprGachaPrizes->isEmpty()) {
            return [];
        }

        $prizesByGroup = $oprGachaPrizes->groupBy('group_id');
        $prizeResources = $oprGachaPrizes->map(fn ($prize) => $prize->prize_resource);
        $prizeInfos = $this->getRewardInfos($prizeResources);

        $tableRows = [];
        foreach ($oprStepupGachaSteps as $oprStepupGachaStep) {
            $fixedPrizeGroupId = $stepFixedPrizeGroupMap->get($oprStepupGachaStep->step_number);
            if (is_null($fixedPrizeGroupId)) {
                continue;
            }
            $prizes = $prizesByGroup->get($fixedPrizeGroupId, collect());
            $totalWeight = $prizes->sum('weight');
            if ($totalWeight === 0) {
                continue;
            }
            $stepRows = [];
            foreach ($prizes as $prize) {
                $prizeInfo = $prizeInfos->get($prize->id);
                if (is_null($prizeInfo)) {
                    continue;
                }
                $probability = $this->calcProbabilityPercent($prize->weight, $totalWeight);
                $stepRows[] = [
                    '報酬情報' => [$prizeInfo],
                    '重み' => $prize->weight,
                    '確率' => "{$probability}%",
                    'pickup' => $prize->pickup ? 'あり' : 'なし',
                ];
            }
            $tableRows[$oprStepupGachaStep->step_number] = [
                'fixed_prize_group_id' => $fixedPrizeGroupId,
                'fixed_prize_count' => $oprStepupGachaStep->fixed_prize_count,
                'threshold' => $oprStepupGachaStep->fixed_prize_rarity_threshold_type?->value ?? 'なし',
                'rows' => $stepRows,
            ];
        }

        return $tableRows;
    }

    private function getStepupGachaStepPrizeGroups(): array
    {
        $prizeTableRows = $this->getStepupGachaPrizeTableRows();
        $fixedPrizeTableRows = $this->getStepupGachaFixedPrizeTableRows();

        $stepNumbers = array_unique(array_merge(
            array_keys($prizeTableRows),
            array_keys($fixedPrizeTableRows)
        ));
        sort($stepNumbers);

        $groups = [];
        foreach ($stepNumbers as $stepNumber) {
            $groups[$stepNumber] = [
                'prize' => $prizeTableRows[$stepNumber] ?? ['prize_group_id' => null, 'rows' => []],
                'fixed_prize' => $fixedPrizeTableRows[$stepNumber] ?? null,
            ];
        }

        return $groups;
    }

    private function calcProbabilityPercent(int $weight, int $totalWeight): float
    {
        return MathUtil::floorToPrecision(($weight / $totalWeight) * 100, 3);
    }
}
