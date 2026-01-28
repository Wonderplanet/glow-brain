<?php

declare(strict_types=1);

namespace App\Domain\Stage\Models\Eloquent;

use App\Domain\Resource\Usr\Models\UsrEloquentModel;

/**
 * @property string $usr_user_id
 * @property string $mst_stage_id
 * @property int $clear_count
 * @property int|null $clear_time_ms
 */
class UsrStage extends UsrEloquentModel
{
}
