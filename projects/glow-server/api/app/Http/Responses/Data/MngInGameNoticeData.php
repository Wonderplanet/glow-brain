<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

class MngInGameNoticeData
{
    public function __construct(
        private string $id,
        private string $displayType,
        private string $destinationType,
        private string $destinationPath,
        private string $destinationPathDetail,
        private int $priority,
        private string $displayFrequencyType,
        private string $title,
        private string $description,
        private string $bannerUrl,
        private string $buttonTitle,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'id' => $this->id,
            'displayType' => $this->displayType,
            'destinationType' => $this->destinationType,
            'destinationPath' => $this->destinationPath,
            'destinationPathDetail' => $this->destinationPathDetail,
            'priority' => $this->priority,
            'displayFrequencyType' => $this->displayFrequencyType,
            'title' => $this->title,
            'description' => $this->description,
            'bannerUrl' => $this->bannerUrl,
            'buttonTitle' => $this->buttonTitle,
        ];
    }
}
