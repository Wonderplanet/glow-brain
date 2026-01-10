<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Shop\Models\UsrShopItem;
use App\Domain\Shop\Models\UsrShopPass;
use App\Domain\Shop\Models\UsrStoreProduct;

class DeleteUsrShopUseCase extends BaseCommands
{
    protected string $name = 'ショップアイテム、パスデータの削除';
    protected string $description = 'ショップアイテム、パスデータを削除します。';

    public function __construct()
    {
    }

    /**
     * デバッグ機能: ショップ、ショップパスの購入り歴を削除します。
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        //ユーザーの所持しているアイテムを削除
        UsrShopItem::query()
            ->where('usr_user_id', $user->id)
            ->delete();

        UsrShopPass::query()
            ->where('usr_user_id', $user->id)
            ->delete();

        UsrStoreProduct::query()
            ->where('usr_user_id', $user->id)
            ->delete();
    }
}
