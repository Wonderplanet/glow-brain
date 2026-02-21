<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstShopPassI18nEntity
{
    public function __construct(
        private string $id,
        private string $mst_shop_pass_id,
        private string $language,
        private string $name,
        private int $release_key
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstShopPassId(): string
    {
        return $this->mst_shop_pass_id;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getReleaseKey(): int
    {
        return $this->release_key;
    }
}
