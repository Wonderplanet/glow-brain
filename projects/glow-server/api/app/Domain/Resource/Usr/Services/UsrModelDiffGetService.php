<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Services;

use App\Domain\Emblem\Repositories\UsrEmblemRepository;
use App\Domain\Encyclopedia\Repositories\UsrArtworkFragmentRepository;
use App\Domain\Encyclopedia\Repositories\UsrArtworkRepository;
use App\Domain\Gacha\Repositories\UsrGachaRepository;
use App\Domain\IdleIncentive\Models\UsrIdleIncentiveInterface;
use App\Domain\IdleIncentive\Repositories\UsrIdleIncentiveRepository;
use App\Domain\InGame\Repositories\UsrEnemyDiscoveryRepository;
use App\Domain\Item\Repositories\UsrItemRepository;
use App\Domain\Unit\Repositories\UsrUnitRepository;
use App\Infrastructure\UsrModelManager;
use Illuminate\Support\Collection;

/**
 * UsrModelの変更差分を取得するサービス
 *
 * UsrModelManagerで管理されている変更差分情報を元に取得を行う
 *
 * 各種UsrModelRepositoryをuseしているが、ロジック実装のためには使用しない。
 * getChangedUsrModelsの引数指定のためだけに使用している。
 */
class UsrModelDiffGetService
{
    public function __construct(
        private UsrModelManager $usrModelManager,
    ) {
    }

    private function getChangedUsrModels(string $usrModelRepositoryClass): Collection
    {
        return $this->usrModelManager->getChangedModels($usrModelRepositoryClass);
    }

    public function getChangedUsrItems(): Collection
    {
        return $this->getChangedUsrModels(UsrItemRepository::class);
    }

    public function getChangedUsrEmblems(): Collection
    {
        return $this->getChangedUsrModels(UsrEmblemRepository::class);
    }

    public function getChangedUsrUnits(): Collection
    {
        return $this->getChangedUsrModels(UsrUnitRepository::class);
    }

    public function getChangedUsrArtworks(): Collection
    {
        return $this->getChangedUsrModels(UsrArtworkRepository::class);
    }

    public function getChangedUsrArtworkFragments(): Collection
    {
        return $this->getChangedUsrModels(UsrArtworkFragmentRepository::class);
    }

    public function getChangedUsrGachas(): Collection
    {
        return $this->getChangedUsrModels(UsrGachaRepository::class);
    }

    public function getChangedUsrEnemyDiscoveries(): Collection
    {
        return $this->getChangedUsrModels(UsrEnemyDiscoveryRepository::class);
    }

    public function getChangedUsrIdleIncentive(): ?UsrIdleIncentiveInterface
    {
        return $this->getChangedUsrModels(UsrIdleIncentiveRepository::class)->first();
    }
}
