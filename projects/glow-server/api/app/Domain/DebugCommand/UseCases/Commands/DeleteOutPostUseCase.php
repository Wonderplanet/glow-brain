<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Outpost\Models\UsrOutpostEnhancement;

class DeleteOutPostUseCase extends BaseCommands
{
    protected string $name = '所持ゲート強化項目の初期化';
    protected string $description = '所持ゲート強化項目を初期化します';

    public function __construct()
    {
    }

    /**
     * デバッグ機能: 所持ゲートの初期化
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {

        //ユーザーの所持ゲート強化項目の初期化
        UsrOutpostEnhancement::query()
            ->where('usr_user_id', $user->id)
            ->update(['level' => 1]);
    }
}
