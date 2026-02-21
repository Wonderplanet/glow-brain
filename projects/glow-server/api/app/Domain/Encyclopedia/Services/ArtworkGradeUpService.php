<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Utils\StringUtil;
use App\Domain\Encyclopedia\Models\LogArtworkGradeUp;
use App\Domain\Encyclopedia\Models\UsrArtworkInterface;
use App\Domain\Encyclopedia\Repositories\LogArtworkGradeUpRepository;
use App\Domain\Encyclopedia\Repositories\UsrArtworkRepository;
use App\Domain\Item\Delegators\ItemDelegator;
use App\Domain\Resource\Entities\LogTriggers\JoinLogTrigger;
use App\Domain\Resource\Mst\Entities\MstArtworkEntity;
use App\Domain\Resource\Mst\Entities\MstArtworkGradeUpCostEntity;
use App\Domain\Resource\Mst\Entities\MstArtworkGradeUpEntity;
use App\Domain\Resource\Mst\Repositories\MstArtworkGradeUpCostRepository;
use App\Domain\Resource\Mst\Repositories\MstArtworkGradeUpRepository;
use App\Domain\Resource\Mst\Repositories\MstArtworkRepository;
use Illuminate\Support\Collection;

readonly class ArtworkGradeUpService
{
    public function __construct(
        private MstArtworkGradeUpRepository $mstArtworkGradeUpRepository,
        private MstArtworkGradeUpCostRepository $mstArtworkGradeUpCostRepository,
        private MstArtworkRepository $mstArtworkRepository,
        private UsrArtworkRepository $usrArtworkRepository,
        private LogArtworkGradeUpRepository $logArtworkGradeUpRepository,
        private ItemDelegator $itemDelegator,
        private EncyclopediaMissionTriggerService $encyclopediaMissionTriggerService,
    ) {
    }

    /**
     * 原画のグレードアップを実行
     * @param string $usrUserId
     * @param string $mstArtworkId
     * @return UsrArtworkInterface
     * @throws GameException
     */
    public function gradeUp(string $usrUserId, string $mstArtworkId): UsrArtworkInterface
    {
        // ユーザーが原画を所持しているか確認
        $usrArtwork = $this->usrArtworkRepository->getByMstArtworkId($usrUserId, $mstArtworkId);
        if (is_null($usrArtwork)) {
            throw new GameException(
                ErrorCode::ARTWORK_NOT_OWNED,
                "artwork not owned. (mstArtworkId: $mstArtworkId)"
            );
        }

        $beforeGradeLevel = $usrArtwork->getGradeLevel();

        $usrArtwork->incrementGradeLevel();
        $afterGradeLevel = $usrArtwork->getGradeLevel();

        // 原画マスタを取得
        $mstArtwork = $this->mstArtworkRepository->getById($mstArtworkId, true);

        // 原画個別設定 → シリーズ+レアリティデフォルトのフォールバック検索
        $mstArtworkGradeUp = $this->resolveMstArtworkGradeUp(
            $mstArtwork,
            $afterGradeLevel
        );

        // コストを取得
        $mstArtworkGradeUpCosts = $this->mstArtworkGradeUpCostRepository->getByMstArtworkGradeUpId(
            $mstArtworkGradeUp->getId(),
            true
        );

        // ログ作成
        $logArtworkGradeUp = $this->logArtworkGradeUpRepository->create(
            $usrUserId,
            $mstArtworkId,
            $beforeGradeLevel,
            $afterGradeLevel,
        );

        // コストを消費
        $this->consumeCosts($usrUserId, $mstArtworkGradeUpCosts, $logArtworkGradeUp);

        // ユーザー原画データを保存
        $this->usrArtworkRepository->syncModel($usrArtwork);

        // ミッショントリガー送信
        $this->encyclopediaMissionTriggerService->sendArtworkGradeUpTrigger($usrArtwork);

        return $usrArtwork;
    }

    /**
     * 原画個別設定 → シリーズ+レアリティデフォルトのフォールバックでグレードアップマスタを取得
     * @param MstArtworkEntity $mstArtwork
     * @param int $gradeLevel
     * @return MstArtworkGradeUpEntity
     * @throws GameException
     */
    private function resolveMstArtworkGradeUp(MstArtworkEntity $mstArtwork, int $gradeLevel): MstArtworkGradeUpEntity
    {
        $mstArtworkId = $mstArtwork->getId();
        $mstSeriesId = $mstArtwork->getMstSeriesId();
        $rarity = $mstArtwork->getRarity();

        $entity = $this->mstArtworkGradeUpRepository->getByMstArtworkIdAndGradeLevel($mstArtworkId, $gradeLevel)
            ?? $this->mstArtworkGradeUpRepository->getBySeriesRarityAndGradeLevel($mstSeriesId, $rarity, $gradeLevel);

        if ($entity === null) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_artwork_grade_up record is not found. ' .
                    '(mstArtworkId: %s, mstSeriesId: %s, rarity: %s, grade: %d)',
                    $mstArtworkId,
                    $mstSeriesId,
                    $rarity,
                    $gradeLevel,
                ),
            );
        }

        return $entity;
    }

    /**
     * コストを消費
     * @param string $usrUserId
     * @param Collection<int, MstArtworkGradeUpCostEntity> $mstArtworkGradeUpCosts
     * @param LogArtworkGradeUp $logArtworkGradeUp
     * @throws GameException
     */
    private function consumeCosts(
        string $usrUserId,
        Collection $mstArtworkGradeUpCosts,
        LogArtworkGradeUp $logArtworkGradeUp,
    ): void {
        $consumeCosts = collect();

        // resource_typeごとに集計する必要があるが、現状Itemのみなのでそのまま処理
        foreach ($mstArtworkGradeUpCosts as $mstArtworkGradeUpCost) {
            $mstItemId = $mstArtworkGradeUpCost->getResourceId();
            if (StringUtil::isNotSpecified($mstItemId)) {
                throw new GameException(
                    ErrorCode::MST_NOT_FOUND,
                    'resource_id is required for resourceType: Item',
                );
            }
            $currentAmount = $consumeCosts->get($mstItemId, 0);
            $consumeCosts->put($mstItemId, $currentAmount + $mstArtworkGradeUpCost->getResourceAmount());
        }

        // アイテムを消費
        if ($consumeCosts->isNotEmpty()) {
            $this->itemDelegator->useItemByMstItemIds(
                $usrUserId,
                $consumeCosts,
                new JoinLogTrigger($logArtworkGradeUp),
            );
        }
    }
}
