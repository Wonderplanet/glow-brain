<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstItemI18nEntity
{
    public function __construct(
        private string $id,
        private string $mst_item_id,
        private string $language,
        private string $name,
        private string $description,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstItemId(): string
    {
        return $this->mst_item_id;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
