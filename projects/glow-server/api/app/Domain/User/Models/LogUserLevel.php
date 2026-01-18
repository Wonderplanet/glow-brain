<?php

declare(strict_types=1);

namespace App\Domain\User\Models;

use App\Domain\Resource\Log\Models\LogModel;

/**
 * @property int $before_level
 * @property int $after_level
 */
class LogUserLevel extends LogModel
{
    public function setBeforeLevel(int $beforeLevel): void
    {
        $this->before_level = $beforeLevel;
    }

    public function setAfterLevel(int $afterLevel): void
    {
        $this->after_level = $afterLevel;
    }
}
