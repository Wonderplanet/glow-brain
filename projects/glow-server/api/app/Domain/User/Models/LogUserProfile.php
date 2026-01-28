<?php

declare(strict_types=1);

namespace App\Domain\User\Models;

use App\Domain\Resource\Log\Models\LogModel;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $profile_column
 * @property string $before_value
 * @property string $after_value
 */
class LogUserProfile extends LogModel
{
    use HasFactory;

    public function setProfileColumn(string $profileColumn): void
    {
        $this->profile_column = $profileColumn;
    }

    public function setBeforeValue(string $beforeValue): void
    {
        $this->before_value = $beforeValue;
    }

    public function setAfterValue(string $afterValue): void
    {
        $this->after_value = $afterValue;
    }
}
