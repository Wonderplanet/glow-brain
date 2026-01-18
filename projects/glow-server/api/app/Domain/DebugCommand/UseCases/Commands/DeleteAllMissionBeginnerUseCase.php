<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Models\Eloquent\UsrMissionNormal;
use App\Domain\Mission\Models\UsrMissionStatus;

class DeleteAllMissionBeginnerUseCase extends BaseCommands
{
    protected string $name = '初心者ミッションの一斉削除';
    protected string $description = '初心者ミッションを一斉削除します';

    public function __construct()
    {
    }

    /**
     * デバッグ機能: 初心者ミッションを一斉削除
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        //初心者ミッションの削除
        UsrMissionNormal::query()
            ->where('usr_user_id', $user->id)
            ->where('mission_type', MissionType::BEGINNER->getIntValue())
            ->delete();

        UsrMissionStatus::query()
            ->where('usr_user_id', $user->id)
            ->delete();
    }
}
