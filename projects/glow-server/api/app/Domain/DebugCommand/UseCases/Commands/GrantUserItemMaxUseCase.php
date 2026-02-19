<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Item\Constants\ItemConstant;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Mst\Models\MstItem;
use Ramsey\Uuid\Uuid;

class GrantUserItemMaxUseCase extends BaseCommands
{
    protected string $name = 'ユーザーの所持アイテム付与＆MAX';
    protected string $description = 'ユーザーの所持アイテム付与＆MAXにします';

    public function __construct(
        private Clock $clock,
    ) {
    }

    /**
     * デバッグ機能: Mst_itemsに入っているデータを全て所持アイテムに個数MAXで付与する
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        // まずユーザーの所持しているアイテムを削除
        UsrItem::query()
            ->where('usr_user_id', $user->id)
            ->delete();

        $now = $this->clock->now()->format('Y-m-d H:i:s');
        //アイテムのマスタを取得
        $mstItems = MstItem::query()
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->get();

        // 投入するデータをまとめる
        $usrItems = [];
        foreach ($mstItems as $mstItem) {
            $uuid = (string) Uuid::uuid4();
            $usrItems[] = [
                'id' => $uuid,
                'usr_user_id' => $user->id,
                'mst_item_id' => $mstItem->id,
                'amount' => ItemConstant::MAX_POSESSION_ITEM_COUNT,
                'created_at' => $this->clock->now(),
                'updated_at' => $this->clock->now(),
            ];
        }
        // UsrItemのレコードを一括で挿入
        UsrItem::insert($usrItems);
    }
}
