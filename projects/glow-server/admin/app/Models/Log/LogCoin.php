<?php

namespace App\Models\Log;

use App\Constants\Database;
use App\Contracts\IAthenaModel;
use App\Domain\User\Models\LogCoin as BaseLogCoin;
use App\Dtos\LogTriggerDto;
use App\Traits\AthenaModelTrait;

class LogCoin extends BaseLogCoin implements IAthenaModel
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
