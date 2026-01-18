<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Message\Models\Eloquent\UsrMessage;

class DeleteAllMessageUseCase extends BaseCommands
{
    protected string $name = 'メールBOXの一斉削除';
    protected string $description = 'メールBOXを一斉削除します';

    public function __construct()
    {
    }

    /**
     * デバッグ機能: メールBOXを一斉削除
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        //メールBOXの削除
        UsrMessage::query()
            ->where('usr_user_id', $user->id)
            ->delete();
    }
}
