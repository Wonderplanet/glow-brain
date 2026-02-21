<?php

declare(strict_types=1);

namespace App\Domain\Mission\Models\Eloquent;

use App\Domain\Resource\Usr\Models\UsrEloquentModel;

class UsrMissionLimitedTerm extends UsrEloquentModel
{
    public function getMstMissionId(): string
    {
        return $this->mst_mission_limited_term_id;
    }
}
