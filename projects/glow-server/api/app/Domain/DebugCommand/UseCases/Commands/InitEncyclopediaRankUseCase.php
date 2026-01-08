<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Encyclopedia\Models\UsrReceivedUnitEncyclopediaReward;
use App\Domain\Unit\Models\Eloquent\UsrUnit;

class InitEncyclopediaRankUseCase extends BaseCommands
{
    protected string $name = '図鑑ランクの初期化';
    protected string $description = '図鑑ランクを初期化します。';

    public function __construct()
    {
    }

    /**
     * デバッグ機能: 図鑑ランクの初期化
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        UsrUnit::query()
            ->where('usr_user_id', $user->id)
            ->update(['grade_level' => 0]);

        UsrReceivedUnitEncyclopediaReward::query()
            ->where('usr_user_id', $user->id)
            ->delete();
    }
}
