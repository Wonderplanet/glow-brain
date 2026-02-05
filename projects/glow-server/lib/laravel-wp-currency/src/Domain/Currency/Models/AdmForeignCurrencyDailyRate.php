<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Models;

// use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * 外貨為替相場レコード
 *
 * @property string $id
 * @property integer $year
 * @property integer $month
 * @property integer $day
 * @property string $currency_code
 * @property string $currency
 * @property string $currency_name
 * @property string $tts
 * @property string $ttb
 * @property string $ttm
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class AdmForeignCurrencyDailyRate extends BaseAdminModel
{
    use HasUuids;
    use HasEntityTrait;
}
