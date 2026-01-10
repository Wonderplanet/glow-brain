<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\Stage\Models\Eloquent\UsrStage as BaseUsrStage;
use App\Models\Mst\MstStage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsrStage extends BaseUsrStage
{
    protected $connection = Database::TIDB_CONNECTION;

    public $timestamps = true;

    public function mst_stages(): BelongsTo
    {
        return $this->belongsTo(MstStage::class, 'mst_stage_id', 'id');
    }
}
