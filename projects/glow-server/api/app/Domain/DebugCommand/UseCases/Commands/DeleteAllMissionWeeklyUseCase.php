<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Models\Eloquent\UsrMissionNormal;

class DeleteAllMissionWeeklyUseCase extends BaseCommands
{
    protected string $name = 'ウィークリーミッションの一斉削除';
    protected string $description = 'ウィークリーミッションを一斉削除します';

    public function __construct()
    {
    }

    /**
     * デバッグ機能: ウィークリーミッションを一斉削除
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        //ウィークリーミッションの削除
        UsrMissionNormal::query()
            ->where('usr_user_id', $user->id)
            ->where('mission_type', MissionType::WEEKLY->getIntValue())
            ->delete();
    }
}
