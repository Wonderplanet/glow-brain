<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstMissionBeginnerI18nEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_mission_beginner_id
 * @property string $language
 * @property string $title
 * @property string $description
 */
class MstMissionBeginnerI18n extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $table = 'mst_mission_beginners_i18n';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_mission_beginner_id' => 'string',
        'language' => 'string',
        'title' => 'string',
        'description' => 'string',
        'release_key' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_mission_beginner_id,
            $this->language,
            $this->title,
            $this->description,
        );
    }
}
