<?php

namespace App\Models\Log;

use App\Constants\Database;
use App\Contracts\IAthenaModel;
use App\Domain\Gacha\Models\LogGachaAction as BaseLogGachaAction;
use App\Traits\AthenaModelTrait;

class LogGachaAction extends BaseLogGachaAction implements IAthenaModel
{
    use AthenaModelTrait;

    protected $connection = Database::TIDB_CONNECTION;

    public function log_gacha()
    {
        return $this->hasOne(LogGacha::class, 'nginx_request_id', 'nginx_request_id');
    }
}
