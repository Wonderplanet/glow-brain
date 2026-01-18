<?php

namespace App\Models\Log;

use App\Constants\Database;
use App\Domain\Cheat\Models\LogSuspectedUser as BaseLogSuspectedUser;
use App\Models\Usr\UsrAdventBattle;
use App\Models\Usr\UsrUser;
use App\Models\Usr\UsrUserProfile;
use App\Models\Mst\MstAdventBattle;

class LogSuspectedUser extends BaseLogSuspectedUser
{
    protected $connection = Database::TIDB_CONNECTION;

    public function usr_user()
    {
        return $this->hasOne(UsrUser::class, 'id', 'usr_user_id');
    }

    public function usr_user_profile()
    {
        return $this->hasOne(UsrUserProfile::class, 'usr_user_id', 'usr_user_id');
    }

    public function usr_advent_battle()
    {
        return $this->hasOne(UsrAdventBattle::class, 'usr_user_id', 'usr_user_id');
    }

    public function mst_advent_battle()
    {
        return $this->hasOne(MstAdventBattle::class, 'id', 'target_id');
    }
}
