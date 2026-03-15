<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Resource\Mst\Repositories\MstUserLevelRepository;
use App\Domain\User\Repositories\UsrUserParameterRepository;
use App\Domain\User\Services\UserService;

class AddUserExpUseCase extends BaseCommands
{
    protected string $name = 'プレイヤー経験値の付与';
    protected string $description = '次のレベルに上がる直前までの経験値を付与します';

    public function __construct(
        private MstUserLevelRepository $mstUserLevelRepository,
        private UserService $userService,
        private UsrUserParameterRepository $usrUserParameterRepository,
        private Clock $clock,
    ) {
    }

    /**
     * デバッグ機能: 次のレベルに上がる直前までの経験値を付与
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        $usrUserId = $user->id;

        $usrParameter = $this->usrUserParameterRepository->findByUsrUserId($usrUserId);
        $level = $usrParameter->getLevel();
        $nextMstLevelRecode = $this->mstUserLevelRepository->getByLevel($level + 1);

        // マスタから次のレベルが取得できない場合はなにもしない
        if (is_null($nextMstLevelRecode)) {
            return;
        }

        $nextRequiredExp = $nextMstLevelRecode->getExp();
        $addExp = ($nextRequiredExp - $usrParameter->getExp()) - 1;
        $now = $this->clock->now();

        $this->userService->addExp($usrUserId, $addExp, $now);
    }
}
