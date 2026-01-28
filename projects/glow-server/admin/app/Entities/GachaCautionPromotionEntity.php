<?php

declare(strict_types=1);

namespace App\Entities;

use App\Models\Adm\AdmGachaCaution;
use Illuminate\Support\Collection;

class GachaCautionPromotionEntity
{
    private const KEY_ADM_GACHA_CAUTION = 'admGachaCaution';

    /**
     * @param Collection<AdmGachaCaution> $admGachaCautions
     */
    public function __construct(
        private Collection $admGachaCautions,
    ) {
    }

    public function formatToResponse(): array
    {
        return [
            self::KEY_ADM_GACHA_CAUTION => $this->admGachaCautions
                ->map(fn(AdmGachaCaution $model) => $model->formatToResponse())
                ->values()
                ->all(),
        ];
    }

    public static function createFromResponseArray(array $response): self
    {
        $admGachaCautions = collect($response[self::KEY_ADM_GACHA_CAUTION] ?? [])
            ->map(fn($response) => AdmGachaCaution::createFromResponseArray($response));

        return new self($admGachaCautions);
    }

    public function isEmpty(): bool
    {
        return $this->admGachaCautions->isEmpty();
    }

    /**
     * @return Collection<AdmGachaCaution>
     */
    public function getAdmGachaCautions(): Collection
    {
        return $this->admGachaCautions;
    }
}
