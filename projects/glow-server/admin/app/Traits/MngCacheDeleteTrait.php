<?php

declare(strict_types=1);

namespace App\Traits;

use App\Domain\Resource\Mng\Repositories\MngAssetReleaseVersionRepository;
use App\Domain\Resource\Mng\Repositories\MngClientVersionRepository;
use App\Domain\Resource\Mng\Repositories\MngDeletedMyIdRepository;
use App\Domain\Resource\Mng\Repositories\MngInGameNoticeBundleRepository;
use App\Domain\Resource\Mng\Repositories\MngJumpPlusRewardBundleRepository;
use App\Domain\Resource\Mng\Repositories\MngMasterReleaseVersionRepository;
use App\Domain\Resource\Mng\Repositories\MngMessageBundleRepository;
use App\Domain\Resource\Mst\Repositories\MngContentCloseRepository;

/**
 * Mngキャッシュ削除トレイト
 *
 * Mngデータは管理ツールで作成更新を行う。その際に古いMngキャッシュを削除してAPI側で最新のデータを参照できるようにする必要がある。
 * そのために、このトレイトで処理を共通化した上でキャッシュ削除の処理を行う。
 */
trait MngCacheDeleteTrait
{
    /**
     * マスタデータ投入時にMngキャッシュを削除して、新しいバージョンのマスタデータをAPIで使えるようにする
     * @return void
     */
    public function deleteMngMasterReleaseVersionCache(): void
    {
        /**
         * @var MngMasterReleaseVersionRepository $mngMasterReleaseVersionRepository
         */
        $mngMasterReleaseVersionRepository = app(MngMasterReleaseVersionRepository::class);

        $mngMasterReleaseVersionRepository->deleteAllCache();
    }

    /**
     * アセットデータ投入時にMngキャッシュを削除して、新しいバージョンのアセットデータをAPIで使えるようにする
     * @return void
     */
    public function deleteMngAssetReleaseVersionCache(): void
    {
        /**
         * @var MngAssetReleaseVersionRepository $mngAssetReleaseVersionRepository
         */
        $mngAssetReleaseVersionRepository = app(MngAssetReleaseVersionRepository::class);

        $mngAssetReleaseVersionRepository->deleteAllCache();
    }

    /**
     * インゲームノーティスデータ更新時にMngキャッシュを削除して、新しいIGNデータをAPIで使えるようにする
     * @return void
     */
    public function deleteMngInGameNoticeCache(): void
    {
        /**
         * @var MngInGameNoticeBundleRepository $mngInGameNoticeBundleRepository
         */
        $mngInGameNoticeBundleRepository = app(MngInGameNoticeBundleRepository::class);

        $mngInGameNoticeBundleRepository->deleteAllCache();
    }

    /**
     * ジャンプ+連携報酬データ更新時にMngキャッシュを削除して、新しいジャンプ+連携報酬データをAPIで使えるようにする
     * @return void
     */
    public function deleteMngJumpPlusRewardCache(): void
    {
        /**
         * @var MngJumpPlusRewardBundleRepository $mngJumpPlusRewardBundleRepository
         */
        $mngJumpPlusRewardBundleRepository = app(MngJumpPlusRewardBundleRepository::class);

        $mngJumpPlusRewardBundleRepository->deleteAllCache();
    }

    /**
     * クライアントバージョンデータ更新時にMngキャッシュを削除して、新しいクライアントバージョンデータをAPIで使えるようにする
     * @return void
     */
    public static function deleteMngClientVersionCache(): void
    {
        /**
         * @var MngClientVersionRepository $mngClientVersionRepository
         */
        $mngClientVersionRepository = app(MngClientVersionRepository::class);

        $mngClientVersionRepository->deleteAllCache();
    }

    /**
     * メールボックスデータ更新時にMngキャッシュを削除する
     * (mng_messages, mng_messages_i18n, mng_message_rewards テーブルデータ)
     *
     * @return void
     */
    public function deleteMngMessageCache(): void
    {
        // キャッシュを削除
        /** @var MngMessageBundleRepository $mngMessageBundleRepository */
        $mngMessageBundleRepository = app()->make(MngMessageBundleRepository::class);
        $mngMessageBundleRepository->deleteAllCache();
    }

    public static function deleteMngContentCloseCache(): void
    {
        // キャッシュを削除
        /** @var MngContentCloseRepository $mngContentCloseRepository */
        $mngContentCloseRepository = app()->make(MngContentCloseRepository::class);
        $mngContentCloseRepository->deleteAllCache();
    }

    /**
     * 削除済みMyIDデータ更新時にMngキャッシュを削除する
     * @return void
     */
    public function deleteMngDeletedMyIdCache(): void
    {
        /**
         * @var MngDeletedMyIdRepository $mngDeletedMyIdRepository
         */
        $mngDeletedMyIdRepository = app(MngDeletedMyIdRepository::class);
        $mngDeletedMyIdRepository->deleteAllCache();
    }
}
