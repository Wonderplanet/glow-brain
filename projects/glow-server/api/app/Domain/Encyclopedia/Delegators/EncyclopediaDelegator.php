<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\Delegators;

use App\Domain\Encyclopedia\Repositories\UsrArtworkFragmentRepository;
use App\Domain\Encyclopedia\Repositories\UsrArtworkRepository;
use App\Domain\Encyclopedia\Services\ArtworkConvertService;
use App\Domain\Encyclopedia\Services\EncyclopediaService;
use App\Domain\Resource\Enums\InGameContentType;
use App\Domain\Resource\Usr\Entities\UsrArtworkEntity;
use Illuminate\Support\Collection;

class EncyclopediaDelegator
{
    public function __construct(
        private EncyclopediaService $encyclopediaService,
        private ArtworkConvertService $artworkConvertService,
        private UsrArtworkRepository $usrArtworkRepository,
        private UsrArtworkFragmentRepository $usrArtworkFragmentRepository,
    ) {
    }

    public function getChangedUsrArtworks(): Collection
    {
        return $this->usrArtworkRepository->getChangedModels();
    }

    public function getChangedUsrArtworkFragments(): Collection
    {
        return $this->usrArtworkFragmentRepository->getChangedModels();
    }

    /**
     * @param string      $usrUserId
     * @param string|null $mstArtworkFragmentDropGroupId
     * @param float       $dropRateMultiplier キャンペーンのドロップ率倍率
     * @param int         $lotteryCount
     * @return void
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function acquireArtworkAndArtworkFragments(
        string $usrUserId,
        InGameContentType $inGameContentType,
        string $targetId,
        ?string $mstArtworkFragmentDropGroupId,
        float $dropRateMultiplier,
        int $lotteryCount,
    ): void {
        $this->encyclopediaService->acquireArtworkAndArtworkFragments(
            $usrUserId,
            $inGameContentType,
            $targetId,
            $mstArtworkFragmentDropGroupId,
            $dropRateMultiplier,
            $lotteryCount,
        );
    }

    /**
     * @param string $usrUserId
     * @param string $mstArtworkId
     */
    public function getUsrArtwork(
        string $usrUserId,
        string $mstArtworkId,
    ): ?UsrArtworkEntity {
        return $this->usrArtworkRepository->getByMstArtworkId($usrUserId, $mstArtworkId)?->toEntity();
    }

    /**
     * 指定したユーザーの図鑑関連データを取得する
     *
     * @param string $usrUserId
     * @return Collection<UsrArtworkEntity>
     */
    public function getUsrArtworks(string $usrUserId): Collection
    {
        return $this->usrArtworkRepository->getList($usrUserId)
            ->map(fn ($usrArtwork) => $usrArtwork->toEntity());
    }

    /**
     * Artworkと全てのFragmentを同時に付与する
     *
     * @param string $usrUserId
     * @param Collection<string> $mstArtworkIds
     */
    public function grantArtworksWithFragments(string $usrUserId, Collection $mstArtworkIds): void
    {
        $this->encyclopediaService->grantArtworksWithFragments($usrUserId, $mstArtworkIds);
    }

    /**
     * 重複した原画が配布される場合にコインに変換する
     * @param string $usrUserId
     * @param Collection<\App\Domain\Resource\Entities\Rewards\BaseReward> $rewards
     */
    public function convertDuplicatedArtworkToCoin(
        string $usrUserId,
        Collection $rewards,
    ): void {
        $this->artworkConvertService->convertDuplicatedArtworkToCoin($usrUserId, $rewards);
    }
}
