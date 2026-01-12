<?php

declare(strict_types=1);

namespace App\Entities;

use App\Models\Adm\AdmPromotionTag;
use Illuminate\Support\Collection;

class TagPromotionEntity
{
    private const KEY_ADM_PROMOTION_TAG = 'admPromotionTag';

    /**
     * @param AdmPromotionTag $admPromotionTag
     */
    public function __construct(
        private AdmPromotionTag $admPromotionTag,
    ) {
    }

    public function formatToResponse(): array
    {
        return [
            self::KEY_ADM_PROMOTION_TAG => $this->admPromotionTag->formatToResponse(),
        ];
    }

    public static function createFromResponseArray(array $response): self
    {
        $admPromotionTag = AdmPromotionTag::createFromResponseArray($response[self::KEY_ADM_PROMOTION_TAG]);

        return new self($admPromotionTag);
    }

    public function getAdmPromotionTag(): AdmPromotionTag
    {
        return $this->admPromotionTag;
    }
}
