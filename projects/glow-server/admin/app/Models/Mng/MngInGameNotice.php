<?php

namespace App\Models\Mng;

use App\Constants\Database;
use App\Constants\DestinationType;
use App\Constants\DisplayFrequencyType;
use App\Constants\SystemConstants;
use App\Domain\Resource\Mng\Models\MngInGameNotice as BaseMngInGameNotice;
use App\Models\Adm\AdmInGameNotice;
use App\Models\Adm\AdmUser;
use App\Utils\StringUtil;
use Carbon\CarbonImmutable;

class MngInGameNotice extends BaseMngInGameNotice
{
    protected $connection = Database::MANAGE_DATA_CONNECTION;

    protected $casts = [
        'start_at' => 'datetime:Y-m-d H:i:s',
        'end_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $guarded = [];

    public function mng_in_game_notice_i18n()
    {
        // TODO: hasManyにして全言語データを取得する
        return $this->hasOne(MngInGameNoticeI18n::class, 'mng_in_game_notice_id', 'id');
    }

    public function adm_in_game_notice()
    {
        return $this->hasOne(AdmInGameNotice::class, 'mng_in_game_notice_id', 'id');
    }

    public function getDisplayFrequencyTypeLabelAttribute(): string
    {
        $enum = DisplayFrequencyType::from($this->display_frequency_type);
        if ($enum === null) {
            return '';
        }

        return $enum->label();
    }

    public function isEnable(): bool
    {
        // 数値文字列の場合でも判定できるように厳密比較にはしない
        return $this->enable == 1;
    }

    public function isDraft(): bool
    {
        // 数値文字列の場合でも判定できるように厳密比較にはしない
        return $this->enable == 0;
    }

    public function getAuthorAttribute(): ?AdmUser
    {
        return $this?->adm_in_game_notice?->author;
    }

    public function getAuthorNameAttribute(): string
    {
        return $this->author?->name ?? '';
    }

    public function getDestinationTypeEnumAttribute(): DestinationType
    {
        $enum = DestinationType::tryfrom($this->destination_type ?? '');
        if ($enum === null) {
            $enum = DestinationType::NONE;
        }

        return $enum;
    }

    public function getFormattedStartAtAttribute(): string
    {
        return $this->start_at->format(SystemConstants::VIEW_DATETIME_FORMAT);
    }

    public function getFormattedEndAtAttribute(): string
    {
        return $this->end_at->format(SystemConstants::VIEW_DATETIME_FORMAT);
    }

    public function calcStatus(CarbonImmutable $now): string
    {
        if ($this->isDraft()) {
            return '下書き';
        }

        if ($now < $this->start_at) {
            return '掲載前';
        } elseif ($now > $this->end_at) {
            return '掲載終了';
        } else {
            return '掲載中';
        }
    }

    public function calcStatusBadgeColor(CarbonImmutable $now): string
    {
        $status = $this->calcStatus($now);

        switch ($status) {
            case '下書き':
                return 'gray';
            case '掲載前':
                return 'primary';
            case '掲載中':
                return 'success';
            case '掲載終了':
                return 'info';
            default:
                return 'gray';
        }
    }

    public function getBannerUrlAttribute(): string
    {
        return $this->mng_in_game_notice_i18n?->banner_url ?? '';
    }

    public function hasBanner(): bool
    {
        return StringUtil::isSpecified($this->banner_url);
    }

    public function formatToResponse(): array
    {
        $array = parent::toArray();

        unset($array['mng_in_game_notice_i18n']);

        return $array;
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
