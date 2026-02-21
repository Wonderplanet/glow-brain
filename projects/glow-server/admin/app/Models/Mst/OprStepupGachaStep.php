<?php

declare(strict_types=1);

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\OprStepupGachaStep as BaseOprStepupGachaStep;
use App\Dtos\RewardDto;
use App\Utils\StringUtil;

/**
 * ステップアップガシャステップマスタモデル（admin用）
 *
 * API側のモデルを継承して使用
 */
class OprStepupGachaStep extends BaseOprStepupGachaStep
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    /**
     * prize_group_id 未設定の場合は、opr_gachas.prize_group_id(共通設定)を使用する必要があるので
     * @return bool true: 共通設定を使用する, false: ステップ固有の設定を使用する
     */
    public function useCommonPrizeGroupId(): bool
    {
        return StringUtil::isNotSpecified($this->prize_group_id);
    }

    public function hasFixedPrizeGroup(): bool
    {
        return StringUtil::isSpecified($this->fixed_prize_group_id);
    }

    public function getCostResourceAttribute(): RewardDto
    {
        return new RewardDto(
            $this->id,
            $this->cost_type->value,
            $this->cost_id,
            $this->cost_num,
        );
    }
}
