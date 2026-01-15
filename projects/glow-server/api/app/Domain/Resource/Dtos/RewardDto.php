<?php

declare(strict_types=1);

namespace App\Domain\Resource\Dtos;

/**
 * 報酬の基本情報を保持するデータクラス。
 * 基本的に、BaseRewardでのみ使う。
 */
class RewardDto
{
    /** @var string 報酬タイプ */
    private string $type = '';

    /**
     * 報酬のリソースID
     * 例：
     *  type=Item, resourceId=mstItemId,
     *  type=Coin, resourceId=null,
     *
     * @var string|null
     */
    private ?string $resourceId = null;

    /** @var integer 報酬の数量 */
    private int $amount = 0;

    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount
    ) {
        $this->type = $type;
        $this->resourceId = $resourceId;
        $this->amount = $amount;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getResourceId(): ?string
    {
        return $this->resourceId;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }
}
