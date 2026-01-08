<?php

declare(strict_types=1);

namespace App\Entities;

class RewardInfo
{
    private string $id = '';
    private string $name = '';
    private ?string $resourceId = '';
    private int $amount = 0;
    public ?string $detailUrl = null;
    private string $resourceType;
    private ?string $assetPath = '';
    private ?string $bgPath = '';
    private ?string $rarity = '';

    public function __construct(
        string $id = '',
        string $name,
        ?string $resourceId,
        int $amount,
        ?string $detailUrl = null,
        string $resourceType = '',
        ?string $assetPath,
        ?string $bgPath = '',
        ?string $rarity = '',
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->resourceId = $resourceId;
        $this->amount = $amount;
        $this->detailUrl = $detailUrl;
        $this->resourceType = $resourceType;
        $this->assetPath = $assetPath;
        $this->bgPath = $bgPath;
        $this->rarity = $rarity;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getResourceId(): ?string
    {
        return $this->resourceId;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getDetailUrl(): ?string
    {
        return $this->detailUrl;
    }

    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    public function getAssetPath(): ?string
    {
        return $this->assetPath;
    }

    public function getBgPath(): ?string
    {
        return $this->bgPath;
    }

    public function getRarity(): ?string
    {
        return $this->rarity;
    }

    public function getLabelWithAmount(bool $useAmount = true): string
    {
        if ($useAmount && $this->amount > 1) {
            return $this->name . ' x ' . $this->amount;
        }
        return $this->name;
    }
}
