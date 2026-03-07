<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Traits\UsrModelManagerTrait;
use App\Domain\DebugCommand\UseCases\Commands;

class DebugCommandExecUseCase
{
    use UsrModelManagerTrait;

    /**
     * デバッグコマンドを実行する
     * @param CurrentUser $user
     * @param string $command
     * @param int $platform
     * @param array<string, mixed> $params
     * @return void
     * @throws GameException
     */
    public function exec(CurrentUser $user, string $command, int $platform, array $params = []): void
    {
        try {
            $className = 'App\\Domain\\DebugCommand\\UseCases\\Commands\\' . $command . 'UseCase';
            $instance = app()->make($className);

            // パラメータ付きコマンドかどうかで分岐
            if ($instance instanceof Commands\BaseParameterizedCommands) {
                $instance->execWithParams($user, $platform, $params);
            } else {
                // 従来の固定値コマンド
                $instance->exec($user, $platform);
            }

            $this->saveAll();
        } catch (\Exception $e) {
            throw new GameException(ErrorCode::ADMIN_DEBUG_FAILED, $e->getMessage());
        }
    }
}
