<?php

declare(strict_types=1);

namespace App\Domain\BoxGacha\Models;

use App\Domain\Resource\Log\Models\LogModel;
use App\Domain\Resource\Traits\HasFactory;
use Illuminate\Support\Collection;

/**
 * @property string $log_type
 * @property string $mst_box_gacha_id
 * @property string $draw_prizes
 * @property int|null $total_draw_count
 */
class LogBoxGachaAction extends LogModel
{
    use HasFactory;

    protected $table = 'log_box_gacha_actions';

    public function setLogType(string $logType): void
    {
        $this->log_type = $logType;
    }

    public function getMstBoxGachaId(): string
    {
        return $this->mst_box_gacha_id;
    }

    public function setMstBoxGachaId(string $mstBoxGachaId): void
    {
        $this->mst_box_gacha_id = $mstBoxGachaId;
    }

    /**
     * @return Collection<int, array{mstBoxGachaPrizeId: string, drawCount: int}>
     */
    public function getDrawPrizes(): Collection
    {
        /** @var array<int, array{mstBoxGachaPrizeId: string, drawCount: int}> $prizes */
        $prizes = json_decode($this->draw_prizes, true) ?? [];
        return collect($prizes);
    }

    /**
     * @param Collection<int, array{mstBoxGachaPrizeId: string, drawCount: int}> $drawPrizes
     */
    public function setDrawPrizes(Collection $drawPrizes): void
    {
        $this->draw_prizes = (string)json_encode($drawPrizes->values()->toArray());
    }

    public function getTotalDrawCount(): ?int
    {
        return $this->total_draw_count;
    }

    public function setTotalDrawCount(?int $totalDrawCount): void
    {
        $this->total_draw_count = $totalDrawCount;
    }
}
