<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

use App\Domain\Resource\Enums\InGameContentType;
use App\Domain\Stage\Models\UsrStageSessionInterface;

class UsrStageStatusData extends UsrInGameStatusData
{
    public function __construct(
        ?UsrStageSessionInterface $usrStageSession,
    ) {
        if (!is_null($usrStageSession)) {
            parent::__construct(
                $usrStageSession->isStarted(),
                InGameContentType::STAGE->value,
                $usrStageSession->getMstStageId(),
                $usrStageSession->getPartyNo(),
                $usrStageSession->getContinueCount(),
                $usrStageSession->getDailyContinueAdCount(),
            );
        } else {
            parent::__construct();
        }
    }
}
