<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\Resource\Usr\Entities\UsrUnitEntity;
use App\Http\Responses\Data\UsrParameterData;

class TutorialUnitLevelUpResultData
{
    public function __construct(
        public string $tutorialStatus,
        public ?UsrUnitEntity $usrUnit,
        public UsrParameterData $usrParameterData,
    ) {
    }
}
