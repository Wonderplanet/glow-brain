<?php

declare(strict_types=1);

namespace App\Entities;

use App\Models\Adm\AdmMessageDistributionInput;
use Illuminate\Support\Collection;

class MessageDistributionPromotionEntity
{
    private const KEY_ADM_MESSAGE_DISTRIBUTION_INPUT = 'admMessageDistributionInput';

    /**
     * @param Collection<AdmMessageDistributionInput> $admMessageDistributionInputs
     */
    public function __construct(
        private Collection $admMessageDistributionInputs,
    ) {
    }

    public function formatToResponse(): array
    {
        return [
            self::KEY_ADM_MESSAGE_DISTRIBUTION_INPUT => $this->admMessageDistributionInputs
                ->map(fn(AdmMessageDistributionInput $input) => $input->formatToResponse())
                ->values()
                ->all(),
        ];
    }

    public static function createFromResponseArray(array $response): self
    {
        $admMessageDistributionInputs = collect($response[self::KEY_ADM_MESSAGE_DISTRIBUTION_INPUT] ?? [])
            ->map(fn($item) => AdmMessageDistributionInput::createFromResponseArray($item));

        return new self($admMessageDistributionInputs);
    }

    public function isEmpty(): bool
    {
        return $this->admMessageDistributionInputs->isEmpty();
    }

    /**
     * @return Collection<AdmMessageDistributionInput>
     */
    public function getAdmMessageDistributionInputs(): Collection
    {
        return $this->admMessageDistributionInputs;
    }
}
