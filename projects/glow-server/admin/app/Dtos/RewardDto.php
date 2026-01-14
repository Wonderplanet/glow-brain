<?php

declare(strict_types=1);

namespace App\Dtos;

class RewardDto
{
    private string $id = '';

    /** @var string 報酬タイプ */
    private string $rewardType = '';

    /**
     * 報酬のリソースID
     * 例：
     *  rewardType=Item, resourceId=mstItemId,
     *  rewardType=Coin, resourceId=null,
     *
     * @var string|null
     */
    private ?string $resourceId = null;

    /** @var integer 報酬の数量 */
    private int $amount = 0;

    private array $option = [];

    public function __construct(
        string $id = '',
        string $rewardType,
        ?string $resourceId,
        int $amount,
    ) {
        $this->id = $id;
        $this->rewardType = $rewardType;
        $this->resourceId = $resourceId;
        $this->amount = $amount;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getRewardType(): string
    {
        return $this->rewardType;
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
