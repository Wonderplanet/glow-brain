<?php

declare(strict_types=1);

namespace App\Domain\BoxGacha\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use Illuminate\Support\Collection;

/**
 * @property string $mst_box_gacha_id
 * @property int $reset_count
 * @property int $total_draw_count
 * @property int $draw_count
 * @property int $current_box_level
 * @property string $draw_prizes
 */
class UsrBoxGacha extends UsrEloquentModel implements UsrBoxGachaInterface
{
    use HasFactory;

    protected $fillable = [];

    protected $casts = [
        'reset_count' => 'integer',
        'total_draw_count' => 'integer',
        'draw_count' => 'integer',
        'current_box_level' => 'integer',
    ];

    /** @var Collection<string, int>|null パース済みdraw_prizes */
    private ?Collection $parsedDrawPrizes = null;

    public function init(string $usrUserId, string $mstBoxGachaId): void
    {
        $this->usr_user_id = $usrUserId;
        $this->mst_box_gacha_id = $mstBoxGachaId;
        $this->reset_count = 0;
        $this->total_draw_count = 0;
        $this->draw_count = 0;
        $this->current_box_level = 1;
        $this->draw_prizes = '{}';
    }

    public function makeModelKey(): string
    {
        return $this->usr_user_id . '_' . $this->mst_box_gacha_id;
    }

    public function getMstBoxGachaId(): string
    {
        return $this->mst_box_gacha_id;
    }

    public function getResetCount(): int
    {
        return $this->reset_count;
    }

    public function getTotalDrawCount(): int
    {
        return $this->total_draw_count;
    }

    public function getCurrentBoxLevel(): int
    {
        return $this->current_box_level;
    }

    public function getDrawCount(): int
    {
        return $this->draw_count;
    }

    /**
     * 抽選済み賞品を取得（mstBoxGachaPrizeId => count の連想配列）
     *
     * @return Collection<string, int>
     */
    public function getDrawPrizes(): Collection
    {
        if ($this->parsedDrawPrizes === null) {
            /** @var array<string, int> $prizes */
            $prizes = json_decode($this->draw_prizes, true) ?? [];
            $this->parsedDrawPrizes = collect($prizes);
        }
        return $this->parsedDrawPrizes;
    }

    /**
     * 抽選済み賞品を設定
     *
     * @param Collection<string, int> $drawPrizes mstBoxGachaPrizeId => count の連想配列
     */
    public function setDrawPrizes(Collection $drawPrizes): void
    {
        $this->draw_prizes = (string)json_encode($drawPrizes->toArray());
        $this->parsedDrawPrizes = $drawPrizes;
    }

    /**
     * 抽選回数を更新（総抽選回数・現BOX抽選回数の両方）
     */
    public function incrementDrawCounts(int $count): void
    {
        $this->total_draw_count += $count;
        $this->draw_count += $count;
    }

    /**
     * 抽選済み賞品を追加（O(1)で追加・更新）
     */
    public function addDrawPrize(string $mstBoxGachaPrizeId, int $count): void
    {
        $prizes = $this->getDrawPrizes();
        $currentCount = $prizes->get($mstBoxGachaPrizeId, 0);
        $prizes->put($mstBoxGachaPrizeId, $currentCount + $count);
        $this->setDrawPrizes($prizes);
    }

    /**
     * 特定の賞品の抽選済み回数を取得（O(1)で取得）
     */
    public function getDrawnCountByPrizeId(string $mstBoxGachaPrizeId): int
    {
        return $this->getDrawPrizes()->get($mstBoxGachaPrizeId, 0);
    }

    /**
     * 現在の箱での抽選済み賞品の総数を取得
     */
    public function getCurrentBoxDrawnCount(): int
    {
        return $this->getDrawPrizes()->sum();
    }

    /**
     * BOXをリセット
     */
    public function reset(int $nextBoxLevel): void
    {
        $this->reset_count++;
        $this->current_box_level = $nextBoxLevel;
        $this->draw_count = 0;
        $this->draw_prizes = '{}';
        $this->parsedDrawPrizes = null;
    }
}
