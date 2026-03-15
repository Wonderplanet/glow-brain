<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstArtworkI18nEntity
{
    public function __construct(
        private string $id,
        private string $mst_artwork_id,
        private string $language,
        private string $name,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstArtworkId(): string
    {
        return $this->mst_artwork_id;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
