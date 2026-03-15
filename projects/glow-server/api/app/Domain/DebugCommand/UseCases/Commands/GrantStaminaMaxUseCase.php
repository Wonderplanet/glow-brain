<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Services\UserService;

class GrantStaminaMaxUseCase extends BaseCommands
{
    /**
     * デバッグコマンドでのスタミナ回復量
     */
    public const RECOVERY_STAMINA = UserConstant::MAX_STAMINA_RECOVERY;

    protected string $name = 'スタミナ回復';
    protected string $description = 'スタミナを' . self::RECOVERY_STAMINA . '回復します';

    public function __construct(
        private Clock $clock,
        private UserService $userService
    ) {
    }

    /**
     * デバッグ機能: スタミナを回復する
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        $this->userService->addStamina($user->id, self::RECOVERY_STAMINA, $this->clock->now());
    }
}
