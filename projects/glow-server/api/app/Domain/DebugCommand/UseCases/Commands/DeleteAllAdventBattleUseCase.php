<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\AdventBattle\Models\UsrAdventBattle;
use App\Domain\AdventBattle\Models\UsrAdventBattleSession;
use App\Domain\Common\Entities\CurrentUser;

class DeleteAllAdventBattleUseCase extends BaseCommands
{
    protected string $name = '降臨バトル情報の一斉削除';
    protected string $description = '降臨バトル情報を一斉削除します';

    public function __construct()
    {
    }

    /**
     * デバッグ機能: 降臨バトル情報を一斉削除
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        //降臨バトル情報の削除
        UsrAdventBattle::query()
            ->where('usr_user_id', $user->id)
            ->delete();

        UsrAdventBattleSession::query()
            ->where('usr_user_id', $user->id)
            ->delete();
    }
}
