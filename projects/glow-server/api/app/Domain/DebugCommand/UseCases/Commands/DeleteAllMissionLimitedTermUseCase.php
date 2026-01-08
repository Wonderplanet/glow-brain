<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Mission\Models\Eloquent\UsrMissionLimitedTerm;

class DeleteAllMissionLimitedTermUseCase extends BaseCommands
{
    protected string $name = '期間限定ミッションの一斉削除';
    protected string $description = '期間限定ミッションを一斉削除します';

    public function __construct()
    {
    }

    /**
     * デバッグ機能: 期間限定ミッションを一斉削除
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        //期間限定ミッションの削除
        UsrMissionLimitedTerm::query()
            ->where('usr_user_id', $user->id)
            ->delete();
    }
}
