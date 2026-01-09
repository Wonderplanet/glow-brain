<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Shop\Enums\MstPackCostType;
use App\Domain\Shop\Enums\SaleCondition;

class MstPackEntity
{
    public function __construct(
        private string $id,
        private string $product_sub_id,
        private int $discount_rate,
        private string $pack_type,
        private ?string $sale_condition,
        private ?string $sale_condition_value,
        private ?int $sale_hours,
        private ?int $tradable_count,
        private string $cost_type,
        private int $cost_amount,
        private int $is_recommend,
        private string $asset_key,
        private ?string $pack_decoration,
        private int $is_first_time_free,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }


    public function getProductSubId(): string
    {
        return $this->product_sub_id;
    }

    public function getDiscountRate(): int
    {
        return $this->discount_rate;
    }

    public function getPackType(): string
    {
        return $this->pack_type;
    }
    public function getSaleCondition(): ?string
    {
        return $this->sale_condition;
    }

    public function isStageClear(): bool
    {
        return $this->sale_condition === SaleCondition::STAGE_CLEAR->value;
    }

    public function isUserLevel(): bool
    {
        return $this->sale_condition === SaleCondition::USER_LEVEL->value;
    }

    public function getSaleConditionValue(): ?string
    {
        return $this->sale_condition_value;
    }

    public function getSaleHours(): ?int
    {
        return $this->sale_hours;
    }

    public function getTradableCount(): ?int
    {
        return $this->tradable_count;
    }

    public function getCostType(): string
    {
        return $this->cost_type;
    }

    public function isPaidDiamond(): bool
    {
        return $this->cost_type === MstPackCostType::PAID_DIAMOND->value;
    }

    public function isCash(): bool
    {
        return $this->cost_type === MstPackCostType::CASH->value;
    }

    public function isDiamond(): bool
    {
        return $this->cost_type === MstPackCostType::DIAMOND->value;
    }

    public function isAd(): bool
    {
        return $this->cost_type === MstPackCostType::AD->value;
    }

    public function isFree(): bool
    {
        return $this->cost_type === MstPackCostType::FREE->value;
    }

    public function getCostAmount(): int
    {
        return $this->cost_amount;
    }

    public function getIsRecommend(): int
    {
        return $this->is_recommend;
    }

    public function getAssetKey(): string
    {
        return $this->asset_key;
    }

    public function getPackDecoration(): ?string
    {
        return $this->pack_decoration;
    }

    public function isFirstTimeFree(): bool
    {
        return $this->is_first_time_free === 1;
    }
}
