<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Entities;

class MngMessageI18nEntity
{
    public function __construct(
        private string $id,
        private string $mngMessageId,
        private string $language,
        private string $title,
        private string $body,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMngMessageId(): string
    {
        return $this->mngMessageId;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
