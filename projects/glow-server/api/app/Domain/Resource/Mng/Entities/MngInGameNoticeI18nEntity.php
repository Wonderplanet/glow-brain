<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Entities;

class MngInGameNoticeI18nEntity
{
    public function __construct(
        private string $id,
        private string $mngInGameNoticeId,
        private string $language,
        private string $title,
        private string $description,
        private string $bannerUrl,
        private string $buttonTitle,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMngInGameNoticeId(): string
    {
        return $this->mngInGameNoticeId;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getBannerUrl(): string
    {
        return $this->bannerUrl;
    }

    public function getButtonTitle(): string
    {
        return $this->buttonTitle;
    }
}
