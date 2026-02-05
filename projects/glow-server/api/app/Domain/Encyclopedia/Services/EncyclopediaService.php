<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Factories\LotteryFactory;
use App\Domain\Common\Utils\StringUtil;
use App\Domain\Emblem\Delegators\EmblemDelegator;
use App\Domain\Encyclopedia\Models\UsrArtworkFragmentInterface;
use App\Domain\Encyclopedia\Repositories\LogArtworkFragmentRepository;
use App\Domain\Encyclopedia\Repositories\UsrArtworkFragmentRepository;
use App\Domain\Encyclopedia\Repositories\UsrArtworkRepository;
use App\Domain\Encyclopedia\Repositories\UsrReceivedUnitEncyclopediaRewardRepository;
use App\Domain\Gacha\Entities\NoPrizeContent;
use App\Domain\InGame\Delegators\InGameDelegator;
use App\Domain\Resource\Entities\Rewards\ArtworkFragmentCompletionReward;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Entities\Rewards\EncyclopediaFirstCollectionReward;
use App\Domain\Resource\Entities\Rewards\UnitEncyclopediaReward;
use App\Domain\Resource\Enums\EncyclopediaType;
use App\Domain\Resource\Enums\InGameContentType;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Entities\MstArtworkFragmentEntity;
use App\Domain\Resource\Mst\Repositories\MstArtworkFragmentRepository;
use App\Domain\Resource\Mst\Repositories\MstUnitEncyclopediaRewardRepository;
use App\Domain\Resource\Mst\Services\MstConfigService;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Unit\Delegators\UnitDelegator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

readonly class EncyclopediaService
{
    public function __construct(
        // Delegators
        private RewardDelegator $rewardDelegator,
        private UnitDelegator $unitDelegator,
        private EmblemDelegator $emblemDelegator,
        private InGameDelegator $inGameDelegator,
        // Repositories
        private MstUnitEncyclopediaRewardRepository $mstUnitEncyclopediaRewardRepository,
        private UsrReceivedUnitEncyclopediaRewardRepository $usrReceivedUnitEncyclopediaRewardRepository,
        private LogArtworkFragmentRepository $logArtworkFragmentRepository,
        // Services
        private MstArtworkFragmentRepository $mstArtworkFragmentRepository,
        private UsrArtworkFragmentRepository $usrArtworkFragmentRepository,
        private UsrArtworkRepository $usrArtworkRepository,
        private EncyclopediaMissionTriggerService $encyclopediaMissionTriggerService,
        private MstConfigService $mstConfigService,
        // Factories
        private LotteryFactory $lotteryFactory,
    ) {
    }

    /**
     * @param string     $usrUserId
     * @param Collection $unitEncyclopediaRewardIds
     * @param int        $platform
     * @param CarbonImmutable     $now
     * @return void
     * @throws GameException
     */
    public function receiveReward(
        string $usrUserId,
        Collection $unitEncyclopediaRewardIds,
        int $platform,
        CarbonImmutable $now
    ): void {
        $unitEncyclopediaRewardIds = $unitEncyclopediaRewardIds->unique();
        $this->validateUnitEncyclopediaRank($usrUserId, $unitEncyclopediaRewardIds);
        $this->validateReceived($usrUserId, $unitEncyclopediaRewardIds);

        $rewards = collect();
        $mstUnitEncyclopediaRewards = $this->mstUnitEncyclopediaRewardRepository->getByIds($unitEncyclopediaRewardIds);
        foreach ($mstUnitEncyclopediaRewards as $mstReward) {
            /** @var \App\Domain\Resource\Mst\Entities\MstUnitEncyclopediaRewardEntity $mstReward */
            $rewards->push(
                new UnitEncyclopediaReward(
                    $mstReward->getResourceType(),
                    $mstReward->getResourceId(),
                    $mstReward->getResourceAmount(),
                    $mstReward->getUnitEncyclopediaRank(),
                )
            );
            $this->usrReceivedUnitEncyclopediaRewardRepository->create($usrUserId, $mstReward->getId());
        }
        $this->rewardDelegator->addRewards($rewards);
    }

    /**
     * 報酬受取可能なキャラ図鑑ランクに到達しているか検証する
     * @param string     $usrUserId
     * @param Collection $unitEncyclopediaRewardIds
     * @return void
     * @throws GameException
     */
    private function validateUnitEncyclopediaRank(string $usrUserId, Collection $unitEncyclopediaRewardIds): void
    {
        // usr_unit_summariesからUnit累計グレードアップ回数を取得
        $encyclopediaRank = $this->unitDelegator->getGradeLevelTotalCount($usrUserId);
        $mstUnitEncyclopediaRewards = $this->mstUnitEncyclopediaRewardRepository->getByIds($unitEncyclopediaRewardIds);
        foreach ($mstUnitEncyclopediaRewards as $reward) {
            if ($encyclopediaRank < $reward->getUnitEncyclopediaRank()) {
                // 報酬受取可能なキャラ図鑑ランクに到達していない
                throw new GameException(
                    ErrorCode::ENCYCLOPEDIA_NOT_REACHED_ENCYCLOPEDIA_RANK,
                    "encyclopedia rank not reached. ($encyclopediaRank < {$reward->getUnitEncyclopediaRank()})"
                );
            }
        }
    }

    /**
     * 受取済みのIDが含まれていないか検証する
     * @param string     $usrUserId
     * @param Collection $unitEncyclopediaRewardIds
     * @return void
     * @throws GameException
     */
    private function validateReceived(string $usrUserId, Collection $unitEncyclopediaRewardIds): void
    {
        $receivedIds = $this
            ->usrReceivedUnitEncyclopediaRewardRepository
            ->getByMstUnitEncyclopediaRewardIds($usrUserId, $unitEncyclopediaRewardIds)
            ->map(fn($model) => $model->getMstUnitEncyclopediaRewardId());
        $duplicateIds = $unitEncyclopediaRewardIds->intersect($receivedIds);
        if ($duplicateIds->isNotEmpty()) {
            // 受取済みのものが含まれている
            throw new GameException(
                ErrorCode::ENCYCLOPEDIA_REWARD_RECEIVED,
                sprintf('contains received reward. (%s)', $duplicateIds->implode(','))
            );
        }
    }

    /**
     * 原画のかけらの抽選を実施して、かけらを獲得する。その際に全てのかけらが集まったら原画も自動獲得する
     * @param string      $usrUserId
     * @param string|null $mstArtworkFragmentDropGroupId
     * @param float       $dropRateMultiplier
     * @param int         $lotteryCount
     * @return void
     * @throws GameException
     */
    public function acquireArtworkAndArtworkFragments(
        string $usrUserId,
        InGameContentType $inGameContentType,
        string $targetId,
        ?string $mstArtworkFragmentDropGroupId,
        float $dropRateMultiplier,
        int $lotteryCount = 1
    ): void {
        $newMstArtworkFragments = $this->lotteryArtworkFragment(
            $usrUserId,
            $mstArtworkFragmentDropGroupId,
            $dropRateMultiplier,
            $lotteryCount,
        );

        $newMstArtworkIds = $this->extractCompletableMstArtworkIds(
            $usrUserId,
            $newMstArtworkFragments->map(function (MstArtworkFragmentEntity $mstArtworkFragment) {
                return $mstArtworkFragment->getMstArtworkId();
            })->unique(),
            collect(),
        );

        // 完成報酬を付与
        foreach ($newMstArtworkIds as $mstArtworkId) {
            $this->usrArtworkRepository->create($usrUserId, $mstArtworkId);
        }
        // ミッショントリガー送信
        $this->encyclopediaMissionTriggerService->sendNewArtworkTrigger($newMstArtworkIds);

        // ログ保存
        $this->createLogArtworkFragments(
            $usrUserId,
            $inGameContentType,
            $targetId,
            $newMstArtworkFragments,
            $newMstArtworkIds,
        );
    }

    /**
     * 原画のかけら収集ログを作成する。原画が完成したタイミングであれば、完成フラグを立てる。
     * @param Collection<MstArtworkFragmentEntity> $newMstArtworkFragments 新規獲得した原画のかけらのコレクション
     * @param Collection<string> $newMstArtworkIds 原画のかけらが集まって新規獲得した原画のIDのコレクション
     * @return void
     */
    private function createLogArtworkFragments(
        string $usrUserId,
        InGameContentType $inGameContentType,
        string $targetId,
        Collection $newMstArtworkFragments,
        Collection $newMstArtworkIds,
    ): void {
        $logArtworkFragments = collect();

        $newMstArtworkIds = $newMstArtworkIds->mapWithKeys(fn($mstArtworkId) => [
            $mstArtworkId => true,
        ]);
        foreach ($newMstArtworkFragments as $newMstArtworkFragment) {
            $isCompleteArtwork = $newMstArtworkIds->get($newMstArtworkFragment->getMstArtworkId(), false);
            $logArtworkFragments->push(
                $this->logArtworkFragmentRepository->make(
                    $usrUserId,
                    $newMstArtworkFragment->getId(),
                    $inGameContentType->value,
                    $targetId,
                    $isCompleteArtwork,
                )
            );
        }

        $this->logArtworkFragmentRepository->addModels($logArtworkFragments);
    }

    /**
     * 原画のかけらのドロップ抽選を行い、ドロップした未所持かけらを獲得する
     * @param string      $usrUserId
     * @param string|null $mstArtworkFragmentDropGroupId
     * @param float       $dropRateMultiplier
     * @param int         $lotteryCount
     * @return Collection<MstArtworkFragmentEntity> 新規獲得した原画のかけら
     * @throws GameException
     */
    private function lotteryArtworkFragment(
        string $usrUserId,
        ?string $mstArtworkFragmentDropGroupId,
        float $dropRateMultiplier,
        int $lotteryCount = 1
    ): Collection {
        if (is_null($mstArtworkFragmentDropGroupId)) {
            return collect();
        }

        $mstArtworkFragments = $this->mstArtworkFragmentRepository->getByDropGroupId(
            $mstArtworkFragmentDropGroupId
        );

        $mstArtworkFragmentIds = $mstArtworkFragments->map(fn($mstArtworkFragment) => $mstArtworkFragment->getId());
        $usrArtworkFragmentIds = $this
            ->usrArtworkFragmentRepository
            ->getByMstArtworkFragmentIds($usrUserId, $mstArtworkFragmentIds)
            ->keyBy(function ($usrArtworkFragment): string {
                return $usrArtworkFragment->getMstArtworkFragmentId();
            })
            ->map(fn($usrArtworkFragment) => $usrArtworkFragment->getMstArtworkFragmentId());

        $newMstArtworkFragments = collect();
        foreach ($mstArtworkFragments as $mstArtworkFragment) {
            /** @var MstArtworkFragmentEntity $mstArtworkFragment */
            if (!is_null($usrArtworkFragmentIds->get($mstArtworkFragment->getId()))) {
                // 獲得済みのためスキップ
                continue;
            }

            // 抽選機で原画のかけらドロップを抽選する
            // ドロップ率は最大100%なので100の不足分を非ドロップとして扱う
            $dropPercentage = min(100, (int) round($mstArtworkFragment->getDropPercentage() * $dropRateMultiplier));
            $lottery = $this->lotteryFactory->createFromMapWithNoPrize(
                weightMap: collect([$mstArtworkFragment->getId() => $dropPercentage]),
                contentMap: collect([$mstArtworkFragment->getId() => $mstArtworkFragment]),
                noPrizeWeight: 100 - $dropPercentage
            );

            $isGet = false;
            for ($i = 0; $i < $lotteryCount; $i++) {
                $result = $lottery->draw();
                if (!($result instanceof NoPrizeContent)) {
                    $isGet = true;
                    break;
                }
            }

            if ($isGet) {
                // 抽選結果がハズレ枠ではなければかけらを獲得する
                $this->usrArtworkFragmentRepository->create(
                    $usrUserId,
                    $mstArtworkFragment->getMstArtworkId(),
                    $mstArtworkFragment->getId()
                );
                $newMstArtworkFragments->add($mstArtworkFragment);
            }
        }
        return $newMstArtworkFragments;
    }

    /**
     * 対象の原画が未完成だった場合に、獲得済みのかけらを確認して、完成できる原画があれば、その原画IDを返す
     * @param string     $usrUserId
     * @param Collection<string> $mstArtworkIds mst_artworks.id
     * @param Collection<string> $extraMstArtworkFragmentIds 追加で獲得予定の原画かけらID mst_artwork_fragments.id
     *      報酬でかけらを獲得する場合など、まだusr_artwork_fragmentsに保存されていないが、
     *      獲得予定のかけらを考慮して原画完成判定を行いたい場合に使用する
     * @return Collection<string> かけらが全て集まって新規獲得した原画ID mst_artworks.id
     * @throws GameException
     */
    private function extractCompletableMstArtworkIds(
        string $usrUserId,
        Collection $mstArtworkIds,
        Collection $extraMstArtworkFragmentIds
    ): Collection {
        if ($mstArtworkIds->isEmpty()) {
            return collect();
        }

        $mstArtworkIds = $mstArtworkIds->unique();
        $hasUsrArtworkIds = $this
            ->usrArtworkRepository
            ->getByMstArtworkIds($usrUserId, $mstArtworkIds)
            ->map(fn($usrArtwork) => $usrArtwork->getMstArtworkId());

        if ($hasUsrArtworkIds->count() === $mstArtworkIds->count()) {
            // かけらを獲得した原画は獲得済み
            return collect();
        }

        // かけらを獲得した原画のすべてのかけらを取得する
        $groupedMstArtworkFragments = $this
            ->mstArtworkFragmentRepository
            ->getByMstArtworkIds($mstArtworkIds)
            ->groupBy(function ($mstArtwork): string {
                return $mstArtwork->getMstArtworkId();
            });

        // 追加で獲得予定のかけらを取得する
        $extraGroupedMstArtworkFragments = $this
            ->mstArtworkFragmentRepository
            ->getByIds($extraMstArtworkFragmentIds)
            ->groupBy(function ($mstArtwork): string {
                return $mstArtwork->getMstArtworkId();
            });

        // かけらを獲得した原画のユーザーが所持しているかけらを取得する
        $usrArtworkFragments = $this
            ->usrArtworkFragmentRepository
            ->getByMstArtworkIds($usrUserId, $mstArtworkIds)
            ->groupBy(function ($mstArtwork): string {
                return $mstArtwork->getMstArtworkId();
            });

        $firstCompletedMstArtworkIds = collect();
        foreach ($groupedMstArtworkFragments as $mstArtworkId => $mstArtworkFragments) {
            /** @var Collection $mstArtworkFragments */

            if ($hasUsrArtworkIds->contains($mstArtworkId)) {
                // 原画は獲得済み
                continue;
            }

            $allMstArtworkFragmentIds = $mstArtworkFragments->map(
                function (MstArtworkFragmentEntity $mstArtworkFragment) {
                    return $mstArtworkFragment->getId();
                }
            );

            // 対象の原画で持っているかけらを絞り込む
            $hasFragmentIds = $usrArtworkFragments
                ->get($mstArtworkId, collect())
                ->map(fn($usrArtworkFragment) => $usrArtworkFragment->getMstArtworkFragmentId());

            $hasFragmentIds = $hasFragmentIds->merge(
                $extraGroupedMstArtworkFragments
                    ->get($mstArtworkId, collect())
                    ->map(fn($mstArtworkFragment) => $mstArtworkFragment->getId())
            )->unique();

            if ($allMstArtworkFragmentIds->diff($hasFragmentIds)->isEmpty()) {
                $firstCompletedMstArtworkIds->add($mstArtworkId);
            }
        }

        return $firstCompletedMstArtworkIds;
    }

    /**
     * 図鑑初取得リワードを処理
     * @param string     $usrUserId
     * @param string     $encyclopediaType
     * @param string     $encyclopediaId
     * @return void
     * @throws GameException
     */
    public function receiveFirstCollectionReward(
        string $usrUserId,
        string $encyclopediaType,
        string $encyclopediaId,
    ): void {
        // 受け取ったタイプ、IDで更新するアイテムを取得してis_new_encyclopediaが1なら、0に更新
        $this->updateIsNewEncyclopediaData($usrUserId, $encyclopediaType, $encyclopediaId);

        // 無償プリズムを規定数配布
        $this->rewardDelegator->addReward(
            new EncyclopediaFirstCollectionReward(
                RewardType::FREE_DIAMOND->value,
                null,
                $this->mstConfigService->getEncyclopediaFirstCollectionReward(),
                $encyclopediaType,
                $encyclopediaId,
            )
        );
    }

    /**
     * 受け取ったタイプ、IDで更新するアイテムを取得してis_new_encyclopediaが1なら、0に更新
     * @param string $usrUserId
     * @param string $encyclopediaType
     * @param string $encyclopediaId
     * @return void
     * @throws GameException
     */
    private function updateIsNewEncyclopediaData(
        string $usrUserId,
        string $encyclopediaType,
        string $encyclopediaId,
    ): void {

        if ($encyclopediaType === EncyclopediaType::ARTWORK->value) {
            // Artwork
            $this->artworkMarkAsCollected($usrUserId, $encyclopediaId);
        } elseif ($encyclopediaType === EncyclopediaType::UNIT->value) {
            // Unit
            $this->unitDelegator->markAsCollected($usrUserId, $encyclopediaId);
        } elseif ($encyclopediaType === EncyclopediaType::ENEMY_DISCOVERY->value) {
            // EnemyDiscovery
            $this->inGameDelegator->markAsCollected($usrUserId, $encyclopediaId);
        } elseif ($encyclopediaType === EncyclopediaType::EMBLEM->value) {
            // Emblem
            $this->emblemDelegator->markAsCollected($usrUserId, $encyclopediaId);
        } else {
            // それ以外はエラー
            throw new GameException(
                ErrorCode::INVALID_PARAMETER,
                'encyclopedia type not found. (' . $encyclopediaType . ')'
            );
        }
    }

    /**
     * 未所持の原画を直接配布し、配布した原画を構成するかけらの未所持分を全て配布する
     *
     * @param string $usrUserId
     * @param Collection<string> $mstArtworkIds
     */
    public function grantArtworksWithFragments(string $usrUserId, Collection $mstArtworkIds): void
    {
        if ($mstArtworkIds->isEmpty()) {
            return;
        }

        $mstArtworkIds = $mstArtworkIds->unique();

        // 既に所持しているArtworkをフィルタリング
        $usrArtworks = $this->usrArtworkRepository->getByMstArtworkIds($usrUserId, $mstArtworkIds);
        $ownedMstArtworkIds = $usrArtworks->map(fn($usrArtwork) => $usrArtwork->getMstArtworkId());

        $newMstArtworkIds = $mstArtworkIds->diff($ownedMstArtworkIds);

        if ($newMstArtworkIds->isEmpty()) {
            return;
        }

        $mstArtworkFragments = $this->mstArtworkFragmentRepository->getByMstArtworkIds($newMstArtworkIds);
        $mstArtworkFragmentIds = $mstArtworkFragments
            ->map(function (MstArtworkFragmentEntity $mstArtworkFragment) {
                return $mstArtworkFragment->getId();
            });

        $this->createUnownedUsrArtworkFragments($usrUserId, $mstArtworkFragmentIds);

        // 新規Artworkモデルを生成
        $newUsrArtworkModels = collect();
        foreach ($newMstArtworkIds as $mstArtworkId) {
            $newUsrArtworkModels->push(
                $this->usrArtworkRepository->make($usrUserId, $mstArtworkId)
            );
        }

        // Artworkをまとめて保存
        if ($newUsrArtworkModels->isNotEmpty()) {
            $this->usrArtworkRepository->syncModels($newUsrArtworkModels);
        }

        // ミッショントリガー送信
        if ($newMstArtworkIds->isNotEmpty()) {
            $this->encyclopediaMissionTriggerService->sendNewArtworkTrigger($newMstArtworkIds);
        }
    }

    /**
     * 指定した原画のかけらの中で、未所持のもののデータを作成する。
     * ログ作成はしない。ユーザーデータの作成のみ。
     * @param string $usrUserId
     * @param Collection<string> $mstArtworkFragmentIds
     * @return Collection<UsrArtworkFragmentInterface>
     */
    public function createUnownedUsrArtworkFragments(
        string $usrUserId,
        Collection $mstArtworkFragmentIds,
    ): Collection {
        $mstArtworkFragments = $this->mstArtworkFragmentRepository->getByIds($mstArtworkFragmentIds);
        if ($mstArtworkFragments->isEmpty()) {
            return collect();
        }

        $mstArtworkFragmentIds = $mstArtworkFragments
            ->map(function (MstArtworkFragmentEntity $mstArtworkFragment) {
                return $mstArtworkFragment->getId();
            });

        $ownedUsrArtworkFragments = $this->usrArtworkFragmentRepository->getByMstArtworkFragmentIds(
            $usrUserId,
            $mstArtworkFragmentIds
        );

        if ($ownedUsrArtworkFragments->count() === $mstArtworkFragments->count()) {
            // 全て所持しているので何もしない
            return collect();
        }

        // 未所持かけらのユーザーデータを生成
        $newUsrArtworkFragments = collect();
        $newMstArtworkFragments = collect();
        foreach ($mstArtworkFragments as $mstArtworkFragment) {
            /** @var MstArtworkFragmentEntity $mstArtworkFragment */
            if ($ownedUsrArtworkFragments->has($mstArtworkFragment->getId())) {
                // 既に所持しているためスキップ
                continue;
            }

            $newMstArtworkFragments->push($mstArtworkFragment);

            $newUsrArtworkFragments->push(
                $this->usrArtworkFragmentRepository->make(
                    $usrUserId,
                    $mstArtworkFragment->getMstArtworkId(),
                    $mstArtworkFragment->getId()
                )
            );
        }

        if ($newUsrArtworkFragments->isNotEmpty()) {
            $this->usrArtworkFragmentRepository->syncModels($newUsrArtworkFragments);
        }

        return $newUsrArtworkFragments;
    }

    /**
     * Artworkの図鑑を取得済みにする
     * @param string $usrUserId
     * @param string $mstArtworkId
     * @throws GameException
     */
    public function artworkMarkAsCollected(string $usrUserId, string $mstArtworkId): void
    {
        $artworkData = $this->usrArtworkRepository->getByMstArtworkId($usrUserId, $mstArtworkId);

        // データがない
        if (is_null($artworkData)) {
            throw new GameException(
                ErrorCode::ENCYCLOPEDIA_DATA_NOT_FOUND,
                'artwork encyclopedia is new data not found. (' . $mstArtworkId . ')'
            );
        }
        // 取得したデータのis_new_encyclopediaが1かどうか
        if ($artworkData->isAlreadyCollected()) {
            // 取得したデータのis_new_encyclopediaが1でない
            throw new GameException(
                ErrorCode::ENCYCLOPEDIA_NOT_IS_NEW,
                'artwork encyclopedia not is new data . (' . $mstArtworkId . ')'
            );
        }
        $artworkData->markAsCollected();
        $this->usrArtworkRepository->syncModel($artworkData);
    }

    /**
     * 配布予定の原画のかけらを考慮して、原画が完成する予定の場合は報酬リストに原画を追加する
     *
     * @param string $usrUserId
     * @param Collection<\App\Domain\Resource\Entities\Rewards\BaseReward> $rewards
     * @return void
     */
    public function addArtworkRewardWhenArtworkCompleted(string $usrUserId, Collection $rewards): void
    {
        $mstArtworkFragmentIds = collect();
        /**
         * @var Collection<string, Collection<BaseReward>> key: mst_artwork_fragments.id
         */
        $artworkFragmentRewardsByMstArtworkFragmentId = [];
        foreach ($rewards as $reward) {
            if ($reward->getType() !== RewardType::ARTWORK_FRAGMENT->value) {
                continue;
            }

            $resourceId = $reward->getResourceId();
            if (StringUtil::isNotSpecified($resourceId)) {
                continue;
            }

            $mstArtworkFragmentIds->put($resourceId, $resourceId);

            $artworkFragmentRewardsByMstArtworkFragmentId[$resourceId][] = $reward;
        }

        if ($mstArtworkFragmentIds->isEmpty()) {
            return;
        }

        $mstArtworkFragments = $this->mstArtworkFragmentRepository->getByIds($mstArtworkFragmentIds->keys());
        if ($mstArtworkFragments->isEmpty()) {
            return;
        }

        // 報酬がどの原画のものかがわかるように配列を構築
        $artworkFragmentRewardsByMstArtworkId = [];
        foreach ($mstArtworkFragments as $mstArtworkFragment) {
            /** @var MstArtworkFragmentEntity $mstArtworkFragment */
            $targetRewards = $artworkFragmentRewardsByMstArtworkFragmentId[$mstArtworkFragment->getId()] ?? null;
            if (is_null($targetRewards)) {
                continue;
            }
            /** @var array<BaseReward> $targetRewards */
            $artworkFragmentRewardsByMstArtworkId[$mstArtworkFragment->getMstArtworkId()] = array_merge(
                $artworkFragmentRewardsByMstArtworkId[$mstArtworkFragment->getMstArtworkId()] ?? [],
                $targetRewards,
            );
        }

        // 原画完成判定を行い、完成する原画IDを取得
        $mstArtworkIds = $mstArtworkFragments->map(function (MstArtworkFragmentEntity $fragment) {
            return $fragment->getMstArtworkId();
        })->unique();
        $completableMstArtworkIds = $this->extractCompletableMstArtworkIds(
            $usrUserId,
            $mstArtworkIds,
            $mstArtworkFragmentIds,
        );

        // 完成する原画があれば報酬リストに追加
        foreach ($completableMstArtworkIds as $mstArtworkId) {
            $reward = new ArtworkFragmentCompletionReward(
                $mstArtworkId,
                1,
            );
            $rewards->put($reward->getId(), $reward);

            // log_artwork_fragments.is_complete_artworkのデータをtriggerOptionに設定して流用
            $targetRewards = $artworkFragmentRewardsByMstArtworkId[$mstArtworkId] ?? [];
            foreach ($targetRewards as $targetReward) {
                $targetReward->setLogTriggerOption((string) true);
                $rewards->put($targetReward->getId(), $targetReward);
            }
        }
    }
}
