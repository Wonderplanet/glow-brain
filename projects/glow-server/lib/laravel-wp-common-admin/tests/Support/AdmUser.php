<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Support;

/**
 * テストで使用する為に作成
 */
class AdmUser extends \WonderPlanet\Domain\Admin\Models\BaseAdmModel
{
    protected $table = 'adm_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'google_id',
        'active',
        'first_name',
        'last_name',
        'avatar',
    ];
}
