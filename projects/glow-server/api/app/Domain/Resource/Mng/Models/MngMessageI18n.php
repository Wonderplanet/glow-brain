<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Models;

use App\Domain\Resource\Mng\Entities\MngMessageI18nEntity;

/**
 * @property string $id
 * @property string $mng_message_id
 * @property string $language
 * @property string $title
 * @property string $body
 */
class MngMessageI18n extends MngModel
{
    protected $table = 'mng_messages_i18n';

    public $timestamps = false;

    protected $casts = [
        'id' => 'string',
        'mng_message_id' => 'string',
        'language' => 'string',
        'title' => 'string',
        'body' => 'string',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'mng_message_id',
        'language',
        'title',
        'body',
    ];

    public function toEntity(): MngMessageI18nEntity
    {
        return new MngMessageI18nEntity(
            $this->id,
            $this->mng_message_id,
            $this->language,
            $this->title,
            $this->body,
        );
    }
}
