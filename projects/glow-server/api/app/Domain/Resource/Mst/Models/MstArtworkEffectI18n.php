<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $mst_artwork_id
 * @property string $language
 * @property string $grade_level1_effect_text
 * @property string $grade_level2_effect_text
 * @property string $grade_level3_effect_text
 * @property string $grade_level4_effect_text
 * @property string $grade_level5_effect_text
 * @property int    $release_key
 */
class MstArtworkEffectI18n extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'mst_artwork_effects_i18n';

    protected $casts = [
        'id' => 'string',
        'mst_artwork_id' => 'string',
        'language' => 'string',
        'grade_level1_effect_text' => 'string',
        'grade_level2_effect_text' => 'string',
        'grade_level3_effect_text' => 'string',
        'grade_level4_effect_text' => 'string',
        'grade_level5_effect_text' => 'string',
        'release_key' => 'integer',
    ];
}
