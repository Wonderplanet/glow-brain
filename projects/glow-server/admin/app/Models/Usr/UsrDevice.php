<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\Auth\Models\UsrDevice as BaseUsrDevice;

class UsrDevice extends BaseUsrDevice
{
    protected $connection = Database::TIDB_CONNECTION;

    public function user()
    {
        return $this->belongsTo(UsrUser::class, 'usr_user_id', 'id');
    }
}

