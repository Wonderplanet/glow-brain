<?php

declare(strict_types=1);

namespace App\Domain\Item\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use Carbon\CarbonImmutable;

/**
 * @property string $usr_user_id
 * @property string $mst_item_id
 * @property int $trade_amount
 * @property int $reset_trade_amount
 * @property string $trade_amount_reset_at
 */
class UsrItemTrade extends UsrEloquentModel implements UsrItemTradeInterface
{
    use HasFactory;

    protected $fillable = [
    ];

    protected $casts = [
    ];

    /**
     * UsrModelManagerでキャッシュ管理する際に使うユニークキーを作成する
     */
    public function makeModelKey(): string
    {
        return $this->usr_user_id . $this->mst_item_id;
    }

    public function getMstItemId(): string
    {
        return $this->mst_item_id;
    }

    public function getTradeAmount(): int
    {
        return $this->trade_amount;
    }

    public function getResetTradeAmount(): int
    {
        return $this->reset_trade_amount;
    }

    public function getTradeAmountResetAt(): string
    {
        return $this->trade_amount_reset_at;
    }

    public function reset(CarbonImmutable $now): void
    {
        $this->reset_trade_amount = 0;
        $this->trade_amount_reset_at = $now->toDateTimeString();
    }

    public function addTradeAmount(int $amount): void
    {
        $this->trade_amount += $amount;
        $this->reset_trade_amount += $amount;
    }
}
