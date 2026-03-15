<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\Pvp\Models\SysPvpSeason as BaseSysPvpSeason;
use App\Models\Mst\MstPvp;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SysPvpSeason extends BaseSysPvpSeason
{
    protected $connection = Database::TIDB_CONNECTION;

    public function mst_pvps(): BelongsTo
    {
        return $this->belongsTo(MstPvp::class, 'mst_pvp_id', 'id');
    }
}
