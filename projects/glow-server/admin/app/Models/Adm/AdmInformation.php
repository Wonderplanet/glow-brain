<?php

namespace App\Models\Adm;

use App\Constants\InformationCategory;
use App\Constants\PublicationStatus;
use App\Constants\SystemConstants;
use App\Utils\StringUtil;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Collection;

class AdmInformation extends AdmModel
{
    use HasUuids;

    protected $casts = [
        'html_json' => 'string',
        'start_at' => 'datetime:Y-m-d H:i:s',
        'end_at' => 'datetime:Y-m-d H:i:s',
        'pre_notice_start_at' => 'datetime:Y-m-d H:i:s',
        'post_notice_end_at' => 'datetime:Y-m-d H:i:s',
        'content_change_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $table = 'adm_informations';
    protected $guarded = [];

    public function author()
    {
        return $this->hasOne(AdmUser::class, 'id', 'author_adm_user_id');
    }

    public function approver()
    {
        return $this->hasOne(AdmUser::class, 'id', 'approval_adm_user_id');
    }

    // TODO: 同じメソッドがMngInGameNoticeにもあるのでどちらかの方法でリファクタして共通化したい
    // 1: traitを使って共通化
    // 2: formのDateTimePickerを継承したクラスを用意して、
    //    CarbonImmutableで値を受け取れるformクラスを作り、下記のformattedメソッドを削除する
    public function getFormattedStartAtAttribute(): string
    {
        return $this->start_at->format(SystemConstants::VIEW_DATETIME_FORMAT);
    }

    public function getFormattedEndAtAttribute(): string
    {
        return $this->end_at->format(SystemConstants::VIEW_DATETIME_FORMAT);
    }

    public function getFormattedPreNoticeStartAtAttribute(): string
    {
        return $this->pre_notice_start_at->format(SystemConstants::VIEW_DATETIME_FORMAT);
    }

    public function getFormattedPostNoticeEndAtAttribute(): string
    {
        return $this->post_notice_end_at->format(SystemConstants::VIEW_DATETIME_FORMAT);
    }

    public function getCategoryLabelAttribute(): string
    {
        $enum = InformationCategory::tryFrom($this->category);
        if ($enum === null) {
            return '';
        }

        return $enum->label();
    }

    public function getAuthorNameAttribute(): string
    {
        return $this->author?->name ?? '';
    }

    public function getHtmlString(): string
    {
        return tiptap_converter()->asHTML($this->html_json);
    }

    public function getPlainText(): string
    {
        return tiptap_converter()->asText($this->html_json);
    }

    public function hasBanner(): bool
    {
        return StringUtil::isSpecified($this->banner_url);
    }

    public function isEnable(): bool
    {
        return $this->enable;
    }

    public function getDisplayStatus(CarbonImmutable $now): PublicationStatus
    {
        if ($this->isEnable() === false) {
            return PublicationStatus::PRIVATE;
        }

        $now->setTimezone(SystemConstants::VIEW_TIMEZONE);

        $preNoticeStartAt = CarbonImmutable::parse($this->pre_notice_start_at);
        $startAt = CarbonImmutable::parse($this->start_at);
        $endAt = CarbonImmutable::parse($this->end_at);
        $postNoticeEndAt = CarbonImmutable::parse($this->post_notice_end_at);

        if ($now->isBefore($preNoticeStartAt)) {
            return PublicationStatus::BEFORE_PUB;
        } elseif ($now->isBefore($startAt)) {
            return PublicationStatus::ANNOUNCING;
        } elseif ($now->lte($endAt)) {
            return PublicationStatus::PUBLISHING;
        } elseif ($now->lte($postNoticeEndAt)) {
            return PublicationStatus::POST_PUB;
        } else {
            return PublicationStatus::ENDED;
        }
    }

    /**
     * index.jsonにあるlast_updated_atを現在日時で更新すべきかどうかのフラグ
     * この情報の変動を検知して、クライアント側では、お知らせバッジを表示するかを制御している。
     *
     * true にするのは
     * 「タイトル」「バナー画像」「カテゴリ」「本文」のいずれかに変更があった時
     *
     * @return bool true: 更新必要 false: 更新はいらない
     */
    public function needLastUpdatedAt(): bool
    {
        $oldHtmlJson = json_decode($this->getOriginal('html_json'), true);
        $newHtmlJson = json_decode($this->html_json, true);
        // json内の要素について、順不同で内容が同じなら同じとみなす
        $isHtmlJsonDirty = $oldHtmlJson != $newHtmlJson;


        return $this->isDirty('title')
            || $this->isDirty('banner_url')
            || $this->isDirty('category')
            || $this->isDirty('os_type')
            || $this->isDirty('html')
            || $isHtmlJsonDirty
            || $this->isDirty('enable') && $this->isEnable();
    }

    /**
     * 公開中で掲載終了日時がすぎていない有効なデータを全て取得
     */
    public static function getActives(CarbonImmutable $now): Collection
    {
        return self::query()
            ->where('enable', 1)
            ->where('post_notice_end_at', '>=', $now)
            ->get();
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
