<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Resource\Mst\Entities\MstUnitEntity;
use App\Domain\Resource\Mst\Repositories\MstUnitRepository;
use App\Domain\Unit\Models\UsrUnitInterface;
use App\Domain\Unit\Repositories\UsrUnitRepository;
use App\Domain\Unit\Services\UnitService;

class AddAllUnitUseCase extends BaseCommands
{
    protected string $name = '全ユニット付与';
    protected string $description = 'マスターに登録されてる未所持のユニットを付与します';

    public function __construct(
        private MstUnitRepository $mstUnitRepository,
        private UsrUnitRepository $usrUnitRepository,
        private UnitService $unitService,
    ) {
    }

    /**
     * デバッグ機能: 設定されている未所持のユニットの付与
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        $usrUnits = $this->usrUnitRepository->getListByUsrUserId($user->id);
        $ownedMstUnitIds = $usrUnits->map(fn(UsrUnitInterface $usrUnit) => $usrUnit->getMstUnitId());
        $mstUnits = $this->mstUnitRepository->getAll();
        $mstUnitIds = $mstUnits->map(fn(MstUnitEntity $mstUnit) => $mstUnit->getId());
        $newMstUnitIds = $mstUnitIds->diff($ownedMstUnitIds)->unique()->values();
        $this->unitService->bulkCreate($user->id, $newMstUnitIds);
    }
}
