<?php

namespace App\Models\Log;

use App\Constants\Database;
use App\Contracts\IAthenaModel;
use App\Domain\Stage\Models\LogStageAction as BaseLogStageAction;
use App\Models\Mst\MstStage;
use App\Models\Mst\MstArtwork;
use App\Traits\AthenaModelTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogStageAction extends BaseLogStageAction implements IAthenaModel
{
    use AthenaModelTrait;

    protected $connection = Database::TIDB_CONNECTION;

    public function mst_stage(): BelongsTo
    {
        return $this->belongsTo(MstStage::class, 'mst_stage_id', 'id');
    }

    public function mst_artwork(): BelongsTo
    {
        return $this->belongsTo(MstArtwork::class, 'mst_artwork_id', 'id');
    }
}
