<?php

declare(strict_types=1);

namespace App\Domain\Item\Models;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Usr\Entities\UsrItemEntity;
use App\Domain\Resource\Usr\Models\UsrModel;

class UsrItem extends UsrModel implements UsrItemInterface
{
    protected static string $tableName = 'usr_items';
    protected array $modelKeyColumns = ['usr_user_id', 'mst_item_id'];

    public static function create(string $usrUserId, string $mstItemId, int $amount): UsrItemInterface
    {
        return new self([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => $mstItemId,
            'amount' => $amount,
        ]);
    }

    public function getMstItemId(): string
    {
        return $this->attributes['mst_item_id'];
    }

    public function getAmount(): int
    {
        return $this->attributes['amount'];
    }

    public function addItemAmount(int $addAmount): void
    {
        $this->attributes['amount'] += $addAmount;
    }

    public function setItemAmount(int $itemAmount): void
    {
        $this->attributes['amount'] = $itemAmount;
    }

    public function subtractItemAmount(int $subtractAmount): void
    {
        if ($this->attributes['amount'] < $subtractAmount) {
            // アイテム所持数が不足している場合はエラー
            throw new GameException(ErrorCode::ITEM_AMOUNT_IS_NOT_ENOUGH);
        }
        $this->attributes['amount'] -= $subtractAmount;
    }

    public function toEntity(): UsrItemEntity
    {
        return new UsrItemEntity(
            $this->getUsrUserId(),
            $this->getMstItemId(),
            $this->getAmount(),
        );
    }
}
