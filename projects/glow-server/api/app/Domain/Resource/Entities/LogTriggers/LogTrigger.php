<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\LogTriggers;

use App\Domain\Resource\Dtos\LogTriggerDto;

abstract class LogTrigger
{
    abstract public function getLogTriggerData(): LogTriggerDto;
}
