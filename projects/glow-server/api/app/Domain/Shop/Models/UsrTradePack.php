<?php

declare(strict_types=1);

namespace App\Domain\Shop\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use Carbon\CarbonImmutable;

/**
 * @property string $id
 * @property string $usr_user_id
 * @property string $mst_pack_id
 * @property int    $daily_trade_count
 * @property string $last_reset_at
 */
class UsrTradePack extends UsrEloquentModel implements UsrTradePackInterface
{
    use HasFactory;

    protected $fillable = [
        'id',
        'usr_user_id',
        'mst_pack_id',
        'daily_trade_count',
        'last_reset_at',
    ];

    public function getMstPackId(): string
    {
        return $this->mst_pack_id;
    }

    public function getDailyTradeCount(): int
    {
        return $this->daily_trade_count;
    }

    public function getLastResetAt(): string
    {
        return $this->last_reset_at;
    }

    public function reset(CarbonImmutable $now): void
    {
        $this->daily_trade_count = 0;
        $this->last_reset_at = $now->format('Y-m-d H:i:s');
    }

    public function incrementTradeCount(): void
    {
        $this->daily_trade_count++;
    }
}
