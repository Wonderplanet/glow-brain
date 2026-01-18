<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

class MissionStatusData
{
    public function __construct(
        public bool $isBeginnerMissionCompleted,
    ) {
    }
}
