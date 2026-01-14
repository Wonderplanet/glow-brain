<?php

namespace App\Models\Mng;

use App\Constants\Database;
use App\Constants\Language;
use App\Domain\Resource\Mng\Models\MngInGameNoticeI18n as BaseMngInGameNoticeI18n;
use Carbon\CarbonImmutable;

class MngInGameNoticeI18n extends BaseMngInGameNoticeI18n
{
    protected $connection = Database::MANAGE_DATA_CONNECTION;

    public function getLanguageLabelAttribute(): string
    {
        $enum = Language::from($this->language);
        if ($enum === null) {
            return '';
        }

        return $enum->label();
    }
    public function formatToResponse(): array
    {
        return parent::toArray();
    }

    public static function createFromResponseArray(array $response): self
    {
        $model = new self();
        $model->fill($response);
        return $model;
    }

    public function formatToInsertArray(): array
    {
        $array = $this->toArray();

        $now = CarbonImmutable::now();
        $array['created_at'] = $now;
        $array['updated_at'] = $now;

        return $array;
    }
}
