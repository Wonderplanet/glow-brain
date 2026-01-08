<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Models\Eloquent\UsrMissionNormal;

class DeleteAllMissionAchivementUseCase extends BaseCommands
{
    protected string $name = 'アチーブメントミッションの一斉削除';
    protected string $description = 'アチーブメントミッションを一斉削除します';

    public function __construct()
    {
    }

    /**
     * デバッグ機能: アチーブメントミッションを一斉削除
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        //アチーブメントミッションの削除
        UsrMissionNormal::query()
            ->where('usr_user_id', $user->id)
            ->where('mission_type', MissionType::ACHIEVEMENT->getIntValue())
            ->delete();
    }
}
