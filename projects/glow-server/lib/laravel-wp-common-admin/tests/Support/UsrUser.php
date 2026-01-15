<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Support;

/**
 * テストで使用する為に作成
 */
class UsrUser extends \WonderPlanet\Domain\Admin\Models\BaseUsrModel
{
    protected $table = 'usr_users';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'tutorial_status',
    ];
}
