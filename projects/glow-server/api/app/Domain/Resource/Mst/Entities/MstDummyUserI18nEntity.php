<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstDummyUserI18nEntity
{
    public function __construct(
        private string $id,
        private string $mstDummyUserId,
        private string $language,
        private string $name,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstDummyUserId(): string
    {
        return $this->mstDummyUserId;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getName(): string
    {
        return $this->name;
    }
    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'id' => $this->id,
            'mst_dummy_user_id' => $this->mstDummyUserId,
            'language' => $this->language,
            'name' => $this->name,
        ];
    }
}
