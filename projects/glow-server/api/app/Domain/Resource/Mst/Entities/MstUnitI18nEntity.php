<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstUnitI18nEntity
{
    public function __construct(
        private string $id,
        private string $mst_unit_id,
        private string $language,
        private string $name,
        private string $description,
        private string $detail,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstUnitId(): string
    {
        return $this->mst_unit_id;
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

    public function getDetail(): string
    {
        return $this->detail;
    }
}
