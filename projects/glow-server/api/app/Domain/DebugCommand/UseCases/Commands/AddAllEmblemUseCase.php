<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases\Commands;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Emblem\Models\UsrEmblemInterface;
use App\Domain\Emblem\Repositories\UsrEmblemRepository;
use App\Domain\Emblem\Services\EmblemService;
use App\Domain\Resource\Mst\Entities\MstEmblemEntity;
use App\Domain\Resource\Mst\Repositories\MstEmblemRepository;

class AddAllEmblemUseCase extends BaseCommands
{
    protected string $name = '全エンブレム付与';
    protected string $description = 'マスターに登録されてる未所持のエンブレムを付与します';

    public function __construct(
        private UsrEmblemRepository $usrEmblemRepository,
        private MstEmblemRepository $mstEmblemRepository,
        private EmblemService $emblemService,
    ) {
    }

    /**
     * デバッグ機能: 設定されている未所持エンブレムの付与
     * @param CurrentUser $user
     * @param int $platform
     * @return void
     */
    public function exec(CurrentUser $user, int $platform): void
    {
        // エンブレム付与
        $usrEmblems = $this->usrEmblemRepository->findByUsrUserId($user->id);
        $ownedMstEmblemId = $usrEmblems
            ->map(fn(UsrEmblemInterface $usrEmblem) => $usrEmblem->getMstEmblemId());
        $mstEmblems = $this->mstEmblemRepository->getAll();
        $mstEmblemIds = $mstEmblems->map(fn(MstEmblemEntity $mstEmblem) => $mstEmblem->getId());
        $newEmblemIds = $mstEmblemIds->diff($ownedMstEmblemId)->unique()->values();
        $this->emblemService->addUsrEmblems($user->id, $newEmblemIds);
    }
}
