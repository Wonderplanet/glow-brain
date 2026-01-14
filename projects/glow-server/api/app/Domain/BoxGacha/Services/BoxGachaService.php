<?php

declare(strict_types=1);

namespace App\Domain\BoxGacha\Services;

use App\Domain\BoxGacha\Entities\BoxGachaDrawPrizeLog;
use App\Domain\BoxGacha\Entities\BoxGachaDrawResult;
use App\Domain\BoxGacha\Entities\BoxGachaPrizeStock;
use App\Domain\BoxGacha\Enums\BoxGachaLoopType;
use App\Domain\BoxGacha\Models\UsrBoxGachaInterface;
use App\Domain\BoxGacha\Repositories\LogBoxGachaActionRepository;
use App\Domain\BoxGacha\Repositories\UsrBoxGachaRepository;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Factories\LotteryFactory;
use App\Domain\Resource\Entities\Rewards\BoxGachaReward;
use App\Domain\Resource\Mst\Entities\MstBoxGachaEntity;
use App\Domain\Resource\Mst\Entities\MstBoxGachaPrizeEntity;
use App\Domain\Resource\Mst\Repositories\MstBoxGachaGroupRepository;
use App\Domain\Resource\Mst\Repositories\MstEventRepository;
use App\Domain\Resource\Mst\Services\MstConfigService;
use App\Domain\Reward\Delegators\RewardDelegator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class BoxGachaService
{
    public function __construct(
        private readonly MstBoxGachaGroupRepository $mstBoxGachaGroupRepository,
        private readonly MstEventRepository $mstEventRepository,
        private readonly MstConfigService $mstConfigService,
        private readonly LotteryFactory $lotteryFactory,
        private readonly UsrBoxGachaRepository $usrBoxGachaRepository,
        private readonly RewardDelegator $rewardDelegator,
        private readonly LogBoxGachaActionRepository $logBoxGachaActionRepository,
    ) {
    }

    /**
     * BOXガチャの期間チェック
     *
     * @param MstBoxGachaEntity $mstBoxGacha
     * @param CarbonImmutable $now
     * @throws GameException
     */
    public function validateBoxGachaPeriod(MstBoxGachaEntity $mstBoxGacha, CarbonImmutable $now): void
    {
        $mstEvent = $this->mstEventRepository->getActiveEvent($mstBoxGacha->getMstEventId(), $now);

        if (is_null($mstEvent)) {
            throw new GameException(
                ErrorCode::BOX_GACHA_PERIOD_OUTSIDE,
                sprintf('box gacha event is not in period. (mst_event_id: %s)', $mstBoxGacha->getMstEventId()),
            );
        }
    }

    /**
     * resetCountからbox_levelを計算
     *
     * マスターデータで定義されているboxLevelリストから次のbox_levelを決定する:
     * - resetCount=0 → boxLevels[0]（最初の箱）
     * - resetCount=1 → boxLevels[1]（2番目の箱）
     * - resetCount=N → boxLevels[N] (全箱数以下の場合)
     *
     * 全箱数を超える場合はLoopTypeに応じて決定:
     * - ALL: 全BOXレベルをループ
     * - LAST: 最後のBOXレベルで固定
     * - FIRST: 1番目のBOXレベルで固定
     *
     * @param int $beforeResetCount リセット処理前のリセット回数
     * @param array<int> $boxLevels マスターデータで定義されているboxLevelリスト
     * @param BoxGachaLoopType $loopType ループタイプ
     * @return int
     */
    private function calculateNextBoxLevel(
        int $beforeResetCount,
        array $boxLevels,
        BoxGachaLoopType $loopType
    ): int {
        if ($boxLevels === []) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                'box levels not found for box gacha',
            );
        }

        // ソートしてインデックスを振り直す
        sort($boxLevels);
        $sortedBoxLevels = array_values($boxLevels);

        $levelCount = count($sortedBoxLevels);
        $afterResetCount = $beforeResetCount + 1;

        // まだ全箱を回っていない場合: resetCount番目のboxLevel
        if ($afterResetCount < $levelCount) {
            return $sortedBoxLevels[$afterResetCount];
        }

        // ループに入った場合
        return match ($loopType) {
            BoxGachaLoopType::ALL => $sortedBoxLevels[$afterResetCount % $levelCount],
            BoxGachaLoopType::LAST => $sortedBoxLevels[$levelCount - 1],
            BoxGachaLoopType::FIRST => $sortedBoxLevels[0],
        };
    }

    /**
     * 現在のBOXの残りの在庫数を計算
     *
     * @param Collection<MstBoxGachaPrizeEntity> $mstBoxGachaPrizes
     * @param UsrBoxGachaInterface $usrBoxGacha
     * @return int
     */
    public function calculateRemainingStock(Collection $mstBoxGachaPrizes, UsrBoxGachaInterface $usrBoxGacha): int
    {
        $totalStock = $mstBoxGachaPrizes->sum(
            fn(MstBoxGachaPrizeEntity $mstBoxGachaPrize) => $mstBoxGachaPrize->getStock()
        );

        return $totalStock - $usrBoxGacha->getCurrentBoxDrawnCount();
    }

    /**
     * 抽選可能な賞品リストを取得（在庫が残っているもののみ）
     *
     * @param Collection<MstBoxGachaPrizeEntity> $mstBoxGachaPrizes
     * @param UsrBoxGachaInterface $usrBoxGacha
     * @return Collection<MstBoxGachaPrizeEntity>
     */
    public function getAvailablePrizes(Collection $mstBoxGachaPrizes, UsrBoxGachaInterface $usrBoxGacha): Collection
    {
        return $mstBoxGachaPrizes->filter(function (MstBoxGachaPrizeEntity $mstBoxGachaPrize) use ($usrBoxGacha) {
            $drawnCount = $usrBoxGacha->getDrawnCountByPrizeId($mstBoxGachaPrize->getId());
            return $drawnCount < $mstBoxGachaPrize->getStock();
        })->values();
    }

    /**
     * 抽選回数のバリデーション
     *
     * @param int $drawCount
     * @param int $remainingStock
     * @throws GameException
     */
    public function validateDrawCount(int $drawCount, int $remainingStock): void
    {
        if ($drawCount <= 0) {
            throw new GameException(
                ErrorCode::INVALID_PARAMETER,
                sprintf('draw count must be positive. (draw_count: %d)', $drawCount),
            );
        }

        $maxDrawCount = $this->mstConfigService->getBoxGachaMaxDrawCount();
        if ($drawCount > $maxDrawCount) {
            throw new GameException(
                ErrorCode::BOX_GACHA_EXCEED_DRAW_LIMIT,
                sprintf('draw count exceeds limit. (draw_count: %d, max: %d)', $drawCount, $maxDrawCount),
            );
        }

        if ($drawCount > $remainingStock) {
            throw new GameException(
                ErrorCode::BOX_GACHA_NOT_ENOUGH_STOCK,
                sprintf('not enough stock. (draw_count: %d, remaining: %d)', $drawCount, $remainingStock),
            );
        }
    }

    /**
     * 箱レベルの整合性チェック
     *
     * クライアントから送信された箱レベルがサーバー側のユーザーデータと一致するか確認
     *
     * @param int $requestCurrentBoxLevel クライアントから送信された箱レベル
     * @param UsrBoxGachaInterface $usrBoxGacha ユーザーのBOXガチャデータ
     * @throws GameException
     */
    public function validateCurrentBoxLevel(int $requestCurrentBoxLevel, UsrBoxGachaInterface $usrBoxGacha): void
    {
        $currentBoxLevel = $usrBoxGacha->getCurrentBoxLevel();

        if ($requestCurrentBoxLevel !== $currentBoxLevel) {
            throw new GameException(
                ErrorCode::BOX_GACHA_BOX_LEVEL_MISMATCH,
                sprintf(
                    'box level mismatch. (request: %d, current: %d)',
                    $requestCurrentBoxLevel,
                    $currentBoxLevel
                ),
            );
        }
    }

    /**
     * BOXガチャの抽選を実行し、永続化・報酬登録・ログ作成まで行う
     *
     * @param string $usrUserId
     * @param Collection<MstBoxGachaPrizeEntity> $mstAvailablePrizes
     * @param UsrBoxGachaInterface $usrBoxGacha
     * @param int $drawCount
     * @param string $mstBoxGachaId
     * @return BoxGachaDrawResult 抽選結果（報酬リストとログ用集計データ）
     * @throws GameException
     */
    public function draw(
        string $usrUserId,
        Collection $mstAvailablePrizes,
        UsrBoxGachaInterface $usrBoxGacha,
        int $drawCount,
        string $mstBoxGachaId
    ): void {
        // 抽選実行（usrBoxGachaの更新も含む）
        $drawResult = $this->calculateDrawResult($mstAvailablePrizes, $usrBoxGacha, $drawCount, $mstBoxGachaId);

        // usrBoxGachaを永続化
        $this->usrBoxGachaRepository->syncModel($usrBoxGacha);

        // 報酬を登録
        $this->rewardDelegator->addRewards($drawResult->getRewards());

        // ログ作成
        $this->logBoxGachaActionRepository->createDrawLog(
            $usrUserId,
            $mstBoxGachaId,
            $drawResult->getPrizeLogs(),
            $drawCount,
        );
    }

    /**
     * BOXガチャの抽選結果を計算
     *
     * 抽選実行、usrBoxGachaの更新（抽選回数・取得賞品）、ログ用集計データ生成を行う
     * 永続化や報酬登録は行わない（純粋な計算のみ）
     *
     * @param Collection<MstBoxGachaPrizeEntity> $mstAvailablePrizes
     * @param UsrBoxGachaInterface $usrBoxGacha
     * @param int $drawCount
     * @param string $mstBoxGachaId
     * @return BoxGachaDrawResult 抽選結果（報酬リストとログ用集計データ）
     * @throws GameException
     */
    private function calculateDrawResult(
        Collection $mstAvailablePrizes,
        UsrBoxGachaInterface $usrBoxGacha,
        int $drawCount,
        string $mstBoxGachaId
    ): BoxGachaDrawResult {
        $rewards = collect();
        /** @var array<string, int> $prizeDrawCounts 賞品IDごとの抽選回数（ログ用） */
        $prizeDrawCounts = [];

        // 抽選用データを準備
        $prizeStockMap = $this->buildPrizeStockMap($mstAvailablePrizes, $usrBoxGacha);

        for ($i = 0; $i < $drawCount; $i++) {
            // 抽選実行
            $mstBoxGachaPrizeEntity = $this->lottery($prizeStockMap);

            if (is_null($mstBoxGachaPrizeEntity)) {
                throw new GameException(
                    ErrorCode::BOX_GACHA_NOT_ENOUGH_STOCK,
                    'no available prizes to draw',
                );
            }

            $mstBoxGachaPrizeId = $mstBoxGachaPrizeEntity->getId();

            // 報酬を追加
            $rewards->push(new BoxGachaReward(
                $mstBoxGachaPrizeEntity->getResourceType()->value,
                $mstBoxGachaPrizeEntity->getResourceId(),
                $mstBoxGachaPrizeEntity->getResourceAmount(),
                $mstBoxGachaId,
                $mstBoxGachaPrizeId,
                $i,
            ));

            // ログ用に賞品ごとの抽選回数をカウント
            $prizeDrawCounts[$mstBoxGachaPrizeId] = ($prizeDrawCounts[$mstBoxGachaPrizeId] ?? 0) + 1;

            // ユーザーの取得済み賞品を更新
            $usrBoxGacha->addDrawPrize($mstBoxGachaPrizeId, 1);

            // 在庫マップを更新
            $prizeStockMap[$mstBoxGachaPrizeId]->decrementStock();
            if (!$prizeStockMap[$mstBoxGachaPrizeId]->hasStock()) {
                unset($prizeStockMap[$mstBoxGachaPrizeId]);
            }
        }

        // usrBoxGachaの抽選回数を更新
        $usrBoxGacha->incrementDrawCounts($drawCount);

        // ログ用の賞品別集計データを作成
        $prizeLogs = collect($prizeDrawCounts)->map(
            fn(int $count, string $prizeId) => new BoxGachaDrawPrizeLog($prizeId, $count)
        )->values();

        return new BoxGachaDrawResult($rewards, $prizeLogs);
    }

    /**
     * 賞品在庫マップを構築
     *
     * @param Collection<MstBoxGachaPrizeEntity> $mstAvailablePrizes
     * @param UsrBoxGachaInterface $usrBoxGacha
     * @return array<string, BoxGachaPrizeStock> mst_box_gacha_prize_id => 賞品の残在庫情報
     */
    private function buildPrizeStockMap(Collection $mstAvailablePrizes, UsrBoxGachaInterface $usrBoxGacha): array
    {
        $prizeStockMap = [];
        foreach ($mstAvailablePrizes as $mstBoxGachaPrize) {
            $drawnCount = $usrBoxGacha->getDrawnCountByPrizeId($mstBoxGachaPrize->getId());
            $remainingStock = $mstBoxGachaPrize->getStock() - $drawnCount;
            if ($remainingStock > 0) {
                $prizeStockMap[$mstBoxGachaPrize->getId()] = new BoxGachaPrizeStock(
                    $mstBoxGachaPrize,
                    $remainingStock,
                );
            }
        }
        return $prizeStockMap;
    }

    /**
     * 重み付き抽選を実行
     *
     * @param array<string, BoxGachaPrizeStock> $prizeStockMap
     * @return MstBoxGachaPrizeEntity|null
     */
    private function lottery(array $prizeStockMap): ?MstBoxGachaPrizeEntity
    {
        if (count($prizeStockMap) === 0) {
            return null;
        }

        // 各賞品の残り在庫数を重みとして抽選（配列のまま処理）
        $weightMap = [];
        $contentMap = [];
        foreach ($prizeStockMap as $id => $prizeStock) {
            $weightMap[$id] = $prizeStock->getRemainingStock();
            $contentMap[$id] = $prizeStock->getPrize();
        }

        $lottery = $this->lotteryFactory->createFromMaps(collect($weightMap), collect($contentMap));

        return $lottery->draw();
    }

    /**
     * BOXをリセット
     *
     * @param UsrBoxGachaInterface $usrBoxGacha
     * @param MstBoxGachaEntity $mstBoxGacha
     */
    public function resetBox(UsrBoxGachaInterface $usrBoxGacha, MstBoxGachaEntity $mstBoxGacha): void
    {
        $boxLevels = $this->mstBoxGachaGroupRepository->getBoxLevels($mstBoxGacha->getId());
        $nextBoxLevel = $this->calculateNextBoxLevel(
            $usrBoxGacha->getResetCount(),
            $boxLevels,
            $mstBoxGacha->getLoopTypeEnum()
        );

        $usrBoxGacha->reset($nextBoxLevel);
    }
}
