<?php

declare(strict_types=1);

namespace App\Domain\Exchange\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use Carbon\CarbonImmutable;

/**
 * @property string      $usr_user_id
 * @property string      $mst_exchange_lineup_id
 * @property string      $mst_exchange_id
 * @property int         $trade_count
 * @property string      $reset_at
 * @property string      $created_at
 * @property string      $updated_at
 */
class UsrExchangeLineup extends UsrEloquentModel implements UsrExchangeLineupInterface
{
    use HasFactory;

    protected $fillable = [
        'usr_user_id',
        'mst_exchange_lineup_id',
        'mst_exchange_id',
        'trade_count',
        'reset_at',
    ];

    protected $casts = [
        'usr_user_id' => 'string',
        'mst_exchange_lineup_id' => 'string',
        'mst_exchange_id' => 'string',
        'trade_count' => 'integer',
        'reset_at' => 'string',
    ];

    public function getMstExchangeLineupId(): string
    {
        return $this->mst_exchange_lineup_id;
    }

    public function getMstExchangeId(): string
    {
        return $this->mst_exchange_id;
    }

    public function getTradeCount(): int
    {
        return $this->trade_count;
    }

    public function incrementTradeCount(int $count = 1): void
    {
        $this->trade_count += $count;
    }

    public function canTrade(?int $tradableCount, int $tradeCount = 1): bool
    {
        // 上限なし（null）の場合は常にtrue
        if ($tradableCount === null) {
            return true;
        }

        // 現在の交換数 + これから交換する数が上限以下であればtrue
        return $this->trade_count + $tradeCount <= $tradableCount;
    }

    public function getResetAt(): string
    {
        return $this->reset_at;
    }

    public function resetTradeCount(CarbonImmutable $now): void
    {
        $this->trade_count = 0;
        $this->reset_at = $now->toDateTimeString();
    }

    /**
     * UsrModelManagerでキャッシュする際のユニークキーを作成する
     * 複合主キー対応
     */
    public function makeModelKey(): string
    {
        return $this->usr_user_id . '_' . $this->mst_exchange_lineup_id . '_' . $this->mst_exchange_id;
    }
}
