<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstSeriesI18nEntity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_series_id
 * @property string $language
 * @property string $name
 * @property string $prefix_word
 * @property int $release_key
 */
class MstSeriesI18n extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $table = 'mst_series_i18n';

    protected $casts = [
        'id' => 'string',
        'mst_series_id' => 'string',
        'language' => 'string',
        'name' => 'string',
        'prefix_word' => 'string',
        'release_key' => 'integer',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'mst_series_id',
        'language',
        'name',
        'prefix_word',
        'release_key',
    ];

    public function toEntity(): MstSeriesI18nEntity
    {
        return new MstSeriesI18nEntity(
            $this->id,
            $this->mst_series_id,
            $this->language,
            $this->name,
            $this->prefix_word,
            $this->release_key,
        );
    }
}
