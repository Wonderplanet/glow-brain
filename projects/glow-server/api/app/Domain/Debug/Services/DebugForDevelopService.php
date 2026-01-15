<?php

declare(strict_types=1);

namespace App\Domain\Debug\Services;

use App\Domain\Encyclopedia\Repositories\UsrArtworkRepository;
use App\Domain\Outpost\Constants\OutpostConstant;
use App\Domain\Outpost\Repositories\UsrOutpostRepository;
use App\Domain\Resource\Mst\Services\MstConfigService;

/**
 * API開発途中のデバッグ用のロジックをまとめたサービス
 * TODO: 本番環境では使用しないので、リリース時はこのクラスごと削除して、各所で使っているデバッグ用コードを必ず削除する
 *
 * テストを邪魔しないように、このクラスをモックスれば、既存テストへの悪影響を最小限にできる。
 */
class DebugForDevelopService
{
    public function __construct(
        private MstConfigService $mstConfigService,
        private UsrOutpostRepository $usrOutpostRepository,
        private UsrArtworkRepository $usrArtworkRepository,
    ) {
    }

    /**
     * 原画を登録する
     * @param string $usrUserId
     * @return void
     */
    public function registerArtworks(string $usrUserId): void
    {
        $mstArtworkIds = $this->mstConfigService->getDebugGrantArtworkIds();
        if ($mstArtworkIds->isEmpty()) {
            return;
        }

        foreach ($mstArtworkIds as $mstArtworkId) {
            $this->usrArtworkRepository->create($usrUserId, $mstArtworkId);
        }
    }

    /**
     * ゲートにデフォルトの表紙(原画)を設定する
     * @param string $usrUserId
     * @return void
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function setDefaultOutpostArtwork(string $usrUserId): void
    {
        $mstArtworkId = $this->mstConfigService->getDebugDefaultOutpostArtworkId();
        if ($mstArtworkId === null) {
            return;
        }

        $usrOutpost = $this->usrOutpostRepository->findByMstOutpostId(
            $usrUserId,
            OutpostConstant::INITIAL_OUTPOST_ID
        );
        if ($usrOutpost === null) {
            return;
        }
        $usrOutpost->setMstArtworkId($mstArtworkId);
        $this->usrOutpostRepository->syncModel($usrOutpost);
    }
}
