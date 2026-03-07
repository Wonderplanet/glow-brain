<?php

namespace App\Models\Log;

use App\Constants\Database;
use App\Contracts\IAthenaModel;
use App\Domain\Pvp\Models\LogPvpAction as BaseLogPvpAction;
use App\Models\Usr\SysPvpSeason;
use App\Models\Usr\UsrUser;
use App\Models\Usr\UsrUserProfile;
use App\Traits\AthenaModelTrait;

class LogPvpAction extends BaseLogPvpAction implements IAthenaModel
{
    use AthenaModelTrait;

    protected $connection = Database::TIDB_CONNECTION;

    public function sys_pvp_season()
    {
        return $this->hasOne(SysPvpSeason::class, 'id', 'sys_pvp_season_id');
    }

    public function opponent_user()
    {
        return $this->hasOneThrough(
            UsrUser::class,
            UsrUserProfile::class,
            'my_id',
            'id',
            'opponent_my_id',
            'usr_user_id'
        );
    }

    public function opponent_user_profile()
    {
        return $this->hasOne(UsrUserProfile::class, 'my_id', 'opponent_my_id');
    }
}
