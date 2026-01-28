<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Encyclopedia\Models\UsrArtwork;

class DeleteAllArtworkUseCase extends BaseCommands
{
    protected string $name = '所持原画の一斉削除';
    protected string $description = '所持原画を一斉削除します。';

    public function __construct()
    {
    }

    /**
     * デバッグ機能: 所持原画の一斉削除
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        //ユーザーの原画の削除
        UsrArtwork::query()
            ->where('usr_user_id', $user->id)
            ->delete();
    }
}
