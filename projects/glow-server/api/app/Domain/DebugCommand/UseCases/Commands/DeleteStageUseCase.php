<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Stage\Models\Eloquent\UsrStage;
use App\Domain\Stage\Models\UsrStageEnhance;
use App\Domain\Stage\Models\UsrStageEvent;

class DeleteStageUseCase extends BaseCommands
{
    protected string $name = '解放済みステージの一斉解除';
    protected string $description = '解放済みステージの一斉解除（解放したステージデータを削除）します。';

    public function __construct()
    {
    }

    /**
     * デバッグ機能: 解放済みステージの一斉解除（解放したステージデータを削除）
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        //ユーザーのステージの削除
        UsrStage::query()
            ->where('usr_user_id', $user->id)
            ->delete();

        //ユーザーのイベントステージの削除
        UsrStageEvent::query()
            ->where('usr_user_id', $user->id)
            ->delete();

        //ユーザーのコイン獲得クエストの削除
        UsrStageEnhance::query()
            ->where('usr_user_id', $user->id)
            ->delete();
    }
}
