<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Item\Models\Eloquent\UsrItem;

class DeleteAllItemUseCase extends BaseCommands
{
    protected string $name = '所持アイテムの一斉削除';
    protected string $description = '所持しているアイテムを一斉削除します。';

    public function __construct()
    {
    }

    /**
     * デバッグ機能: 所持しているアイテムを一斉削除します
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        //ユーザーの所持しているアイテムを削除
        UsrItem::query()
            ->where('usr_user_id', $user->id)
            ->delete();
    }
}
