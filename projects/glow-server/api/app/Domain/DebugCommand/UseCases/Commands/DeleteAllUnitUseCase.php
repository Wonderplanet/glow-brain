<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Unit\Models\Eloquent\UsrUnit;

class DeleteAllUnitUseCase extends BaseCommands
{
    protected string $name = '所持ユニットの一斉削除';
    protected string $description = '所持ユニットを一斉削除します';

    public function __construct()
    {
    }

    /**
     * デバッグ機能: 所持ユニットを一斉削除
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        //所持ユニットの削除
        UsrUnit::query()
            ->where('usr_user_id', $user->id)
            ->delete();
    }
}
