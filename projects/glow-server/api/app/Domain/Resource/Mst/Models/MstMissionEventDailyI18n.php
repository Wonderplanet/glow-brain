<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstMissionEventDailyI18nEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property int    $release_key
 * @property string $mst_mission_event_daily_id
 * @property string $language
 * @property string $description
 */
class MstMissionEventDailyI18n extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $table = 'mst_mission_event_dailies_i18n';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_mission_event_daily_id' => 'string',
        'language' => 'string',
        'description' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_mission_event_daily_id,
            $this->language,
            $this->description,
        );
    }
}
