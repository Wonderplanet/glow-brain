<?php

namespace App\Models\Adm;

class AdmUserDeletionOperateHistory extends AdmModel
{

    protected $table = 'adm_user_deletion_operate_histories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'usr_user_id',
        'status',
        'adm_user_id',
        'profile_data',
        'operated_at',
        'expires_at',
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
}
