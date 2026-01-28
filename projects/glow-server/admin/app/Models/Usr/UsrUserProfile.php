<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\User\Models\UsrUserProfile as BaseUsrUserProfile;

class UsrUserProfile extends BaseUsrUserProfile
{
    protected $connection = Database::TIDB_CONNECTION;

    /**
     * Factoryクラスの取得 (デフォルトに戻す)
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        //
    }

    public function usr_users()
    {
        return $this->belongsTo(UsrUser::class, 'id', 'usr_user_id');
    }
}
