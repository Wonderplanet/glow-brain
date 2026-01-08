<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Entities;

use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Entities\OprGachaPrizeEntity;

class GachaPrize extends OprGachaPrizeEntity implements GachaBoxInterface
{
    private string $rarity;

    public function __construct(
        string $id,
        string $groupId,
        RewardType $resourceType,
        ?string $resourceId,
        int $resourceAmount,
        int $weight,
        bool $pickup,
        string $rarity,
    ) {
        parent::__construct($id, $groupId, $resourceType, $resourceId, $resourceAmount, $weight, $pickup);
        $this->rarity = $rarity;
    }

    public function getRarity(): string
    {
        return $this->rarity;
    }

    /**
     * @return array<mixed>
     */
    public function formatToLog(): array
    {
        $resourceId = $this->getResourceId();
        if ($this->getResourceType() === RewardType::COIN) {
            $resourceId = 'coin';
        }
        return [
            'id' => $this->getId(),
            'group_id' => $this->getGroupId(),
            'resource_type' => $this->getResourceType()->value,
            'resource_id' => $resourceId,
            'resource_amount' => $this->getResourceAmount(),
            'weight' => $this->getWeight(),
            'pickup' => $this->getPickup(),
            'rarity' => $this->getRarity(),
        ];
    }

    /**
     * @param array<mixed> $data
     * @return self
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['group_id'],
            RewardType::from($data['resource_type']),
            $data['resource_id'] ?? null,
            $data['resource_amount'],
            $data['weight'],
            $data['pickup'],
            $data['rarity'],
        );
    }
}
