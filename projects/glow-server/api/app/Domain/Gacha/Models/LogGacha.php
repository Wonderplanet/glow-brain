<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Models;

use App\Domain\Resource\Log\Models\LogModel;
use App\Domain\Resource\Traits\HasFactory;
use Illuminate\Support\Carbon;

/**
 * @property string $opr_gacha_id
 * @property string $result
 * @property string $cost_type
 * @property int    $draw_count
 * @property Carbon  $created_at
 * @property int|null $step_number ステップ番号（ステップアップガシャ用、通常ガシャはnull）
 */
class LogGacha extends LogModel
{
    use HasFactory;

    public function getOprGachaId(): string
    {
        return $this->opr_gacha_id;
    }

    public function setOprGachaId(string $oprGachaId): void
    {
        $this->opr_gacha_id = $oprGachaId;
    }

    /**
     * @return array<mixed>
     */
    public function getResult(): array
    {
        return unserialize($this->result);
    }

    /**
     * @param array<mixed> $result
     */
    public function setResult(array $result): void
    {
        $this->result = serialize($result);
    }

    public function getCostType(): string
    {
        return $this->cost_type;
    }

    public function setCostType(string $costType): void
    {
        $this->cost_type = $costType;
    }

    public function getDrawCount(): int
    {
        return $this->draw_count;
    }

    public function setDrawCount(int $drawCount): void
    {
        $this->draw_count = $drawCount;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->created_at;
    }

    /**
     * ステップ番号を取得
     *
     * @return int|null ステップ番号（ステップアップガシャ用、通常ガシャはnull）
     */
    public function getStepNumber(): ?int
    {
        return $this->step_number;
    }

    /**
     * ステップ番号を設定
     *
     * @param int|null $stepNumber ステップ番号
     */
    public function setStepNumber(?int $stepNumber): void
    {
        $this->step_number = $stepNumber;
    }
}
