<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Entities;

class FormatMstPvpRankListResponse
{
    /**
     * @param array<mixed> $formatedRanks
     * @param array<mixed> $targetRankKeys
     */
    public function __construct(
        private array $formatedRanks,
        private array $targetRankKeys,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function getFormatRanks(): array
    {
        return $this->formatedRanks;
    }

    /**
     * @return array<mixed>
     */
    public function getTargetRankKeys(): array
    {
        return $this->targetRankKeys;
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'formatedRanks' => $this->formatedRanks,
            'targetRankKeys' => $this->targetRankKeys,
        ];
    }
}
