<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class OprProductI18nEntity
{
    public function __construct(
        private string $id,
        private string $opr_product_id,
        private string $language,
        private string $asset_key
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOprProductId(): string
    {
        return $this->opr_product_id;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getAssetKey(): string
    {
        return $this->asset_key;
    }
}
