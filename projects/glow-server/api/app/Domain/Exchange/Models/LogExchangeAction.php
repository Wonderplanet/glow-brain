<?php

declare(strict_types=1);

namespace App\Domain\Exchange\Models;

use App\Domain\Resource\Log\Models\LogModel;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $usr_user_id
 * @property string $nginx_request_id
 * @property string $request_id
 * @property int    $logging_no
 * @property string $mst_exchange_lineup_id
 * @property string $mst_exchange_id
 * @property array<mixed> $costs
 * @property array<mixed> $rewards
 * @property int    $trade_count
 * @property string $created_at
 * @property string $updated_at
 */
class LogExchangeAction extends LogModel
{
    use HasFactory;

    protected $fillable = [
        'id',
        'usr_user_id',
        'nginx_request_id',
        'request_id',
        'logging_no',
        'mst_exchange_lineup_id',
        'mst_exchange_id',
        'costs',
        'rewards',
        'trade_count',
    ];

    protected $casts = [
        'id' => 'string',
        'usr_user_id' => 'string',
        'nginx_request_id' => 'string',
        'request_id' => 'string',
        'logging_no' => 'integer',
        'mst_exchange_lineup_id' => 'string',
        'mst_exchange_id' => 'string',
        'costs' => 'array',
        'rewards' => 'array',
        'trade_count' => 'integer',
    ];

    public function getMstExchangeLineupId(): string
    {
        return $this->mst_exchange_lineup_id;
    }

    public function setMstExchangeLineupId(string $mstExchangeLineupId): void
    {
        $this->mst_exchange_lineup_id = $mstExchangeLineupId;
    }

    public function getMstExchangeId(): string
    {
        return $this->mst_exchange_id;
    }

    public function setMstExchangeId(string $mstExchangeId): void
    {
        $this->mst_exchange_id = $mstExchangeId;
    }

    /**
     * @return array<mixed>
     */
    public function getCosts(): array
    {
        return $this->costs;
    }

    /**
     * @param array<mixed> $costs
     */
    public function setCosts(array $costs): void
    {
        $this->costs = $costs;
    }

    /**
     * @return array<mixed>
     */
    public function getRewards(): array
    {
        return $this->rewards;
    }

    /**
     * @param array<mixed> $rewards
     */
    public function setRewards(array $rewards): void
    {
        $this->rewards = $rewards;
    }

    public function getTradeCount(): int
    {
        return $this->trade_count;
    }

    public function setTradeCount(int $tradeCount): void
    {
        $this->trade_count = $tradeCount;
    }

    public function formatToInsert(): array
    {
        $values = parent::formatToInsert();

        // 配列をJSON文字列に変換
        if (isset($values['costs']) && is_array($values['costs'])) {
            $values['costs'] = json_encode($values['costs']);
        }
        if (isset($values['rewards']) && is_array($values['rewards'])) {
            $values['rewards'] = json_encode($values['rewards']);
        }

        return $values;
    }
}
