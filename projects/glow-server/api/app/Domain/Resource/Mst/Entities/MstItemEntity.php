<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Gacha\Entities\GachaPrizeInterface;
use App\Domain\Item\Enums\ItemType;

class MstItemEntity implements GachaPrizeInterface
{
    public function __construct(
        private string $id,
        private string $type,
        private string $group_type,
        private string $rarity,
        private string $asset_key,
        private ?string $effect_value,
        private int $sort_order,
        private string $start_date,
        private string $end_date,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getItemType(): string
    {
        return $this->type;
    }

    public function isIdleBox(): bool
    {
        return $this->isIdleCoinBox() || $this->isIdleRankUpMaterialBox();
    }

    public function isIdleCoinBox(): bool
    {
        return $this->type === ItemType::IDLE_COIN_BOX->value;
    }

    public function isIdleRankUpMaterialBox(): bool
    {
        return $this->type === ItemType::IDLE_RANK_UP_MATERIAL_BOX->value;
    }

    public function isRankUpMaterial(): bool
    {
        return $this->type === ItemType::RANK_UP_MATERIAL->value;
    }

    public function getGroupType(): string
    {
        return $this->group_type;
    }

    public function getRarity(): string
    {
        return $this->rarity;
    }

    public function getAssetKey(): string
    {
        return $this->asset_key;
    }

    public function getEffectValue(): ?string
    {
        return $this->effect_value;
    }

    public function getIdleBoxMinutes(): int
    {
        if ($this->isIdleBox() && !is_null($this->effect_value)) {
            return (int) $this->effect_value;
        }
        return 0;
    }

    public function getSortOrder(): int
    {
        return $this->sort_order;
    }

    public function getStartDate(): string
    {
        return $this->start_date;
    }

    public function getEndDate(): string
    {
        return $this->end_date;
    }
}
