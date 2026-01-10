<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Gacha\Models\UsrGacha;
use App\Domain\Gacha\Models\UsrGachaUpper;

class DeleteAllGachaUseCase extends BaseCommands
{
    protected string $name = 'ガシャの一斉削除';
    protected string $description = 'ガシャを一斉削除します';

    public function __construct()
    {
    }

    /**
     * デバッグ機能: ガシャの一斉削除
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        //ガシャの一斉削除
        UsrGacha::query()
            ->where('usr_user_id', $user->id)
            ->delete();

        //ガシャの天井判定の削除
        UsrGachaUpper::query()
            ->where('usr_user_id', $user->id)
            ->delete();
    }
}
