<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

class OprCampaignData
{
    public function __construct(
        private string $id,
        private string $campaignType,
        private string $targetType,
        private ?string $difficulty,
        private ?string $targetIdType,
        private ?string $targetId,
        private int $effectValue,
        private string $description,
        private string $startAt,
        private string $endAt,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'id' => $this->id,
            'campaignType' => $this->campaignType,
            'targetType' => $this->targetType,
            'difficulty' => $this->difficulty,
            'targetIdType' => $this->targetIdType,
            'targetId' => $this->targetId,
            'effectValue' => $this->effectValue,
            'description' => $this->description,
            'startAt' => $this->startAt,
            'endAt' => $this->endAt,
        ];
    }
}
