<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Models\Eloquent\UsrMissionEvent;

class DeleteAllMissionEventDailyUseCase extends BaseCommands
{
    protected string $name = 'イベントデイリーミッションの一斉削除';
    protected string $description = 'イベントデイリーミッションを一斉削除します';

    public function __construct()
    {
    }

    /**
     * デバッグ機能: イベントデイリーミッションを一斉削除
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        //イベントミッションの削除
        UsrMissionEvent::query()
            ->where('usr_user_id', $user->id)
            ->where('mission_type', MissionType::EVENT_DAILY->getIntValue())
            ->delete();
    }
}
