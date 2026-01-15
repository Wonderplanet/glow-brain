<?php

namespace App\Models\Log;

use App\Constants\Database;
use App\Contracts\IAthenaModel;
use App\Domain\User\Models\LogExp as BaseLogExp;
use App\Dtos\LogTriggerDto;
use App\Traits\AthenaModelTrait;

class LogExp extends BaseLogExp implements IAthenaModel
{
    use AthenaModelTrait;

    protected $connection = Database::TIDB_CONNECTION;

    public function getLogTriggerAttribute(): LogTriggerDto
    {
        return new LogTriggerDto(
            $this->trigger_source,
            $this->trigger_value ?? '',
            $this->trigger_option ?? '',
        );
    }

    public function getLogTriggerKeyAttribute(): string
    {
        return $this->trigger_source . $this->trigger_value;
    }
}
