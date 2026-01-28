<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Model;
use App\Constants\UserStatus;
use Illuminate\Support\Facades\DB;

class AdmUserBanOperateHistory extends Model
{

    protected $table = 'adm_user_ban_operate_histories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'usr_user_id',
        'ban_status',
        'operation_reason',
        'operated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
    ];


    public function getId(): string
    {
        return $this->id;
    }

    public function getBanStatus(): string
    {
        $enum = UserStatus::tryFrom($this->ban_status);
        return $enum?->label() ?? '';
    }

    /**
     * 指定ユーザーに対して時限BAN対応をした履歴を取得する
     */
    public static function getBanTemporariesByUsrUserId(
        string $usrUserId,
        string $banTemporaryTotalCountColumnName = 'temporary_total_count',
    ): self {
        return AdmUserBanOperateHistory::select(
            DB::raw(
                'COUNT(CASE WHEN ban_status IN ('.UserStatus::BAN_TEMPORARY_CHEATING->value.' , '.UserStatus::BAN_TEMPORARY_DETECTED_ANOMALY->value .') THEN 1 END) AS ' . $banTemporaryTotalCountColumnName),
            )
            ->where('usr_user_id', $usrUserId)
            ->first();
    }
}
