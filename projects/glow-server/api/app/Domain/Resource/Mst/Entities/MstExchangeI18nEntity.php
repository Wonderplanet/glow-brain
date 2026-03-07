<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstExchangeI18nEntity
{
    public function __construct(
        private string $id,
        private string $mstExchangeId,
        private string $language,
        private string $name,
        private string $assetKey,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstExchangeId(): string
    {
        return $this->mstExchangeId;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAssetKey(): string
    {
        return $this->assetKey;
    }
}
