<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Constants\GachaAppearanceCondition;
use App\Constants\GachaType;
use App\Constants\ImagePath;
use App\Domain\Resource\Mst\Models\OprGacha as BaseOprGacha;
use App\Utils\StringUtil;
use Illuminate\Support\Collection;

class OprGacha extends BaseOprGacha
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function opr_gacha_i18n()
    {
        return $this->hasOne(OprGachaI18n::class, 'opr_gacha_id', 'id');
    }

    public function opr_gacha_use_resources()
    {
        return $this->hasMany(OprGachaUseResource::class, 'opr_gacha_id', 'id');
    }

    public function opr_gacha_uppers()
    {
        return $this->hasMany(OprGachaUpper::class, 'upper_group', 'upper_group');
    }

    public function opr_gacha_display_unit_i18ns()
    {
        return $this->hasMany(OprGachaDisplayUnitI18n::class, 'opr_gacha_id', 'id');
    }

    public function opr_stepup_gacha()
    {
        return $this->hasOne(OprStepupGacha::class, 'opr_gacha_id', 'id');
    }

    public function getGachaTypeLabelAttribute(): string
    {
        return GachaType::from($this->gacha_type->value)->label();
    }

    public function getAppearanceConditionLabelAttribute(): string
    {
        return GachaAppearanceCondition::from($this->appearance_condition->value)->label();
    }

    public function getName(): string
    {
        return $this->opr_gacha_i18n?->name ?? '';
    }

    public function makeBannerUrl(string $domain): ?string
    {

        return StringUtil::joinPath(
            $domain,
            ImagePath::GACHA_BANNER_PATH->value . $this->opr_gacha_i18n->banner_url . '.png',
        );
    }

    public function getPrizeGroupIds(): Collection
    {
        $prizeGroupIds = collect();

        $entity = $this->toEntity();
        $prizeGroupIds->push($entity->getPrizeGroupId());
        if ($entity->hasFixedPrizeGroup()) {
            $prizeGroupIds->push($entity->getFixedPrizeGroupId());
        }

        return $prizeGroupIds;
    }

    /**
     * ガシャシミュレーションで、前回のシミュレーションからマスタデータに変更があるかどうかを判定するためのデータを整形して返す
     * @return array<mixed>
     */
    public function formatToSimulationCheckData(): array
    {
        $entity = $this->toEntity();

        return [
            'upper_group' => $entity->getUpperGroup(),
            'prize_group_id' => $entity->getPrizeGroupId(),
            'fixed_prize_group_id' => $entity->getFixedPrizeGroupId(),
        ];
    }
}
