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
        $description = $oprGacha->opr_gacha_i18n->description ?? '';

        return [
            'ガシャID' => $oprGacha->id,
            'ガシャ名' => $oprGacha->opr_gacha_i18n->name,
            'ガシャタイプ' => $oprGacha->gacha_type_label,
            'ガシャ訴求文言' => $description,
            '開始日時' => $oprGacha->start_at,
            '終了日時' => $oprGacha->end_at,
        ];
    }

    private function getDisplayInfo(): array
    {
        $oprGacha = $this->getMstModel();
        return [
            '表示順' => $oprGacha->gacha_priority,
            '表示条件' => $oprGacha->appearance_condition_label,
        ];
    }

    private function getAssetInfo(): array
    {
        $oprGacha = $this->getMstModel();

        $bannerUrlDomain = app(ConfigGetService::class)->getS3BannerUrl() ?? '';

        $banner = $oprGacha->makeBannerUrl($bannerUrlDomain);


        $assetInfo = [
            'バナー' => $banner ? $banner : $oprGacha->opr_gacha_i18n->banner_url,
            'バナーID' => $oprGacha->opr_gacha_i18n->banner_url,
            'バナーサイズ' => $oprGacha->opr_gacha_i18n->gacha_banner_size,
        ];

        return $assetInfo;
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
            '通算で引ける上限数' => $oprGacha->total_play_limit_count ?? '無制限',
            '1日に引ける上限数' => $oprGacha->daily_play_limit_count ?? '無制限',
        ];
    }

    private function getDrawByAdInfos(): array
    {
        $oprGacha = $this->getMstModel();
        return [
            '通算で引ける上限数' => $oprGacha->total_ad_limit_count ?? '無制限',
            '1日に引ける上限数' => $oprGacha->daily_ad_limit_count ?? '無制限',
            'インターバル時間 (分)' => $oprGacha->ad_play_interval_time ?? '未設定',
        ];
    }

    private function getMultiDrawInfos(): array
    {
        $oprGacha = $this->getMstModel();
        $oprGachaEntity = $oprGacha->toEntity();

        return [
            'N連数' => $oprGacha->multi_draw_count,
            '確定枠数' => $oprGachaEntity->hasMultiFixedPrize() ? $oprGacha->multi_fixed_prize_count : 'なし',
        ];
    }

    private function getGachaProbabilityInfos(): array
    {
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

        $oprGachaPrizes = OprGachaPrize::query()
            ->where('group_id', $oprGacha->prize_group_id)
            ->orWhere('group_id', $oprGacha->fixed_prize_group_id)
            ->get();

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

        return [
            'ガシャ訴求文言' => $i18n->description ?? '',
            '最高レアリティ天井文言' => $i18n->max_rarity_upper_description ?? '',
            'ピックアップ天井文言' => $i18n->pickup_upper_description ?? '',
            '確定枠文言' => $i18n->fixed_prize_description ?? '',
        ];
    }

    private function calcProbabilityPercent(int $weight, int $totalWeight): float
    {
        return MathUtil::floorToPrecision(($weight / $totalWeight) * 100, 3);
    }
}
