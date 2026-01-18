<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\User\Models\UsrUser as BaseUsrUser;
use App\Constants\UserStatus;

class UsrUser extends BaseUsrUser
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

    public function usr_user_profiles()
    {
        return $this->hasOne(UsrUserProfile::class, 'usr_user_id', 'id');
    }

    public function profile()
    {
        return $this->hasOne(UsrUserProfile::class, 'usr_user_id', 'id');
    }

    public function usr_user_login()
    {
        return $this->hasOne(UsrUserLogin::class, 'usr_user_id', 'id');
    }

    public function usr_user_parameter()
    {
        return $this->hasOne(UsrUserParameter::class, 'usr_user_id', 'id');
    }

    public function usr_device()
    {
        return $this->hasOne(UsrDevice::class, 'usr_user_id', 'id');
    }

    public function usr_store_product_history()
    {
        return $this->hasMany(UsrStoreProductHistory::class, 'usr_user_id', 'id');
    }

    public function usr_pvps()
    {
        return $this->hasMany(UsrPvp::class, 'usr_user_id', 'id');
    }

    public function getUserStatus(): string
    {
        $enum = UserStatus::tryFrom($this->status);
        return $enum?->label() ?? '';
    }
}
