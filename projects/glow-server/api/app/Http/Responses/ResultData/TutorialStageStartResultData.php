<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\UsrStageStatusData;

class TutorialStageStartResultData
{
    public function __construct(
        public string $tutorialStatus,
        public UsrStageStatusData $usrStageStatus,
    ) {
    }
}
