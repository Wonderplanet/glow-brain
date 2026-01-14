<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Unit\Models\UsrUnit;
use App\Domain\Unit\Repositories\UsrUnitRepository;

class InitAllUnitStatusUseCase extends BaseCommands
{
    protected string $name = '所持ユニットのステータス初期化';
    protected string $description = '所持ユニットのステータスを初期化します';

    public function __construct(
        private UsrUnitRepository $usrUnitRepository,
    ) {
    }

    /**
     * デバッグ機能: 所持ユニットを初期化
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        $usrUnits = $this->usrUnitRepository->getListByUsrUserId($user->id);

        //所持ユニットのステータス初期化
        foreach ($usrUnits as $usrUnit) {
            /** @var UsrUnit $usrUnit */
            $usrUnit->init();
        }
        $this->usrUnitRepository->syncModels($usrUnits);
    }
}
