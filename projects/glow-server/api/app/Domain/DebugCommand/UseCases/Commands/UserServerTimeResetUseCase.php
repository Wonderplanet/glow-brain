<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Debug\Repositories\DebugUserTimeSettingRepository;

/**
 * ユーザーサーバー時間リセットコマンド
 */
class UserServerTimeResetUseCase extends BaseCommands
{
    protected string $name = 'ユーザーサーバー時間リセット';
    protected string $description = 'ユーザーのサーバー時間設定をリセットして、通常の時間に戻します';

    public function __construct(
        private DebugUserTimeSettingRepository $debugUserTimeSettingRepository,
    ) {
    }

    public function exec(CurrentUser $user, int $platform): void
    {
        $this->debugUserTimeSettingRepository->delete($user->getId());
    }
}
