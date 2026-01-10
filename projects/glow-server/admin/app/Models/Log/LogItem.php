<?php

namespace App\Models\Log;

use App\Constants\Database;
use App\Contracts\IAthenaModel;
use App\Domain\Item\Models\LogItem as BaseLogItem;
use App\Dtos\LogTriggerDto;
use App\Models\Mst\MstItem;
use App\Traits\AthenaModelTrait;

class LogItem extends BaseLogItem implements IAthenaModel
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

    public function mst_item()
    {
        return $this->hasOne(MstItem::class, 'id', 'mst_item_id');
    }

}
