<?php

declare(strict_types=1);

namespace App\Domain\Reward\Services;

use App\Domain\Common\Exceptions\GameException;
use App\Domain\Emblem\Delegators\EmblemDelegator;
use App\Domain\Emblem\Repositories\LogEmblemRepository;
use App\Domain\Encyclopedia\Delegators\EncyclopediaDelegator;
use App\Domain\Encyclopedia\Repositories\LogArtworkFragmentRepository;
use App\Domain\Item\Repositories\LogItemRepository;
use App\Domain\Message\Constants\MessageConstant;
use App\Domain\Message\Delegator\MessageDelegator;
use App\Domain\Message\Enums\MessageSource;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Entities\RewardSendPolicy;
use App\Domain\Resource\Entities\RewardSendSummary;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Enums\UnreceivedRewardReason;
use App\Domain\Resource\Log\Repositories\Contracts\ILogModelRepositoryRewardSend;
use App\Domain\Reward\Entities\RewardSendContext;
use App\Domain\Reward\Entities\RewardSent;
use App\Domain\Reward\Managers\RewardManagerInterface;
use App\Domain\Reward\Traits\RewardSendServiceTrait;
use App\Domain\Unit\Delegators\UnitDelegator;
use App\Domain\Unit\Repositories\LogUnitRepository;
use App\Domain\User\Repositories\LogCoinRepository;
use App\Domain\User\Repositories\LogExpRepository;
use App\Domain\User\Repositories\LogStaminaRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * 報酬タイプごとに報酬付与を実行するクラス。
 * RewardManagerの報酬付与前リストから報酬オブジェクトを取得し、報酬タイプごとの報酬付与を実行する。
 */
class RewardSendService
{
    use RewardSendServiceTrait;

    /**
     * 報酬付与を実行するクラスと報酬タイプのマッピング。
     * 報酬付与前に、指定された報酬タイプの報酬オブジェクトのみを取得してsendServiceクラスへ渡し、報酬付与を実行する。
     *
     * @var array<mixed>
     */
    private array $sendServiceClasses = [
        RewardType::COIN->value => CoinSendService::class,
        RewardType::FREE_DIAMOND->value => FreeDiamondSendService::class,
        RewardType::STAMINA->value => StaminaSendService::class,
        RewardType::ITEM->value => ItemSendService::class,
        RewardType::EXP->value => ExpSendService::class,
        RewardType::EMBLEM->value => EmblemSendService::class,
        RewardType::UNIT->value => UnitSendService::class,
        RewardType::ARTWORK->value => ArtworkSendService::class,
        RewardType::ARTWORK_FRAGMENT->value => ArtworkFragmentSendService::class,
    ];

    /**
     * 報酬付与ログを保存するリポジトリクラスと報酬タイプのマッピング。
     *
     * @var array<mixed>
     */
    private array $logRepositoryClasses = [
        RewardType::COIN->value => LogCoinRepository::class,
        RewardType::STAMINA->value => LogStaminaRepository::class,
        RewardType::ITEM->value => LogItemRepository::class,
        RewardType::EXP->value => LogExpRepository::class,
        RewardType::EMBLEM->value => LogEmblemRepository::class,
        RewardType::UNIT->value => LogUnitRepository::class,
        RewardType::ARTWORK_FRAGMENT->value => LogArtworkFragmentRepository::class,
    ];

    public function __construct(
        private readonly RewardManagerInterface $rewardManager,
    ) {
    }

    /**
     * 報酬付与の実行を行う。
     * 本メソッドをRewardDelegatorを介して呼び出すことで、報酬付与を実行する。
     *
     * @param string $usrUserId
     * @param int $platform
     * @param CarbonImmutable $now
     */
    public function sendRewards(
        string $usrUserId,
        int $platform,
        CarbonImmutable $now,
        ?RewardSendPolicy $policy = null,
    ): RewardSendSummary {
        if ($policy === null) {
            $policy = RewardSendPolicy::createDefaultPolicy();
        }

        try {
            $rewardSendSummary = $this->execSendRewards(
                usrUserId: $usrUserId,
                platform: $platform,
                now: $now,
                policy: $policy,
            );
        } catch (\Throwable $e) {
            if ($this->isCurrencyOverflowException($e)) {
                // プリズム上限超過時の例外
                $policy->throwResourceLimitReachedExceptionIfSet();
            }

            throw $e; // 指定された例外がない or その他例外はそのまま投げる
        }

        // 報酬送信結果をチェックして、必要に応じて例外を投げる
        $this->checkAndThrowErrorByRewarSendSummary($rewardSendSummary, $policy);

        // 投げる例外がなければ、報酬送信結果を返す
        return $rewardSendSummary;
    }

    /**
     * 報酬送信結果をチェックして、必要に応じて例外を投げる。
     *
     * @throws GameException
     */
    private function checkAndThrowErrorByRewarSendSummary(
        RewardSendSummary $rewardSendSummary,
        RewardSendPolicy $policy,
    ): void {
        $throwErrorRewardTypes = $policy->getRewardTypesOfThrowErrorWhenResourceLimitReached(
            array_keys($this->sendServiceClasses),
        );
        if ($rewardSendSummary->hasResourceOverflow($throwErrorRewardTypes)) {
            $policy->throwResourceLimitReachedExceptionIfSet();
        }
    }

    /**
     * 報酬配布処理を実行する内部メソッド
     */
    private function execSendRewards(
        string $usrUserId,
        int $platform,
        CarbonImmutable $now,
        RewardSendPolicy $policy,
    ): RewardSendSummary {
        $rewardSendSummary = new RewardSendSummary();

        // 報酬付与実行によって、別の追加報酬が発生した場合に、報酬付与を再度実行できるように対応
        // 現状はexp配布によるユーザーレベルアップ報酬のみ想定している。
        for ($i = 0; $i < 2; $i++) {
            if ($this->rewardManager->isNeedSendRewards() === false) {
                break;
            }
            $rewardSendSummary->merge(
                $this->execSendIteration($usrUserId, $platform, $now, $policy)
            );
        }

        return $rewardSendSummary;
    }

    /**
     * 1回分の報酬付与処理をまとめたメソッド。
     * 報酬付与が別の追加報酬のトリガーとなるケースがあるため、1回分のメソッドとして、実際に呼び出すメソッドとは別にしている。
     *
     * @param string $usrUserId
     * @param CarbonImmutable $now
     */
    private function execSendIteration(
        string $usrUserId,
        int $platform,
        CarbonImmutable $now,
        RewardSendPolicy $policy,
    ): RewardSendSummary {
        $rewardSendSummary = new RewardSendSummary();

        // 前処理
        $this->beforeSend($usrUserId, $now);

        // 報酬タイプごとに処理を進める
        /** @var \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<BaseReward>> $rewardTypeGroups */
        $rewardTypeGroups = $this->rewardManager->getNeedToSendRewards()
            ->groupBy(fn(BaseReward $reward) => $reward->getType());

        foreach ($rewardTypeGroups as $type => $typeRewards) {
            if ($typeRewards->isEmpty()) {
                continue;
            }

            $sendService = $this->getSendService($type);
            if ($sendService === null) {
                continue;
            }

            $context = new RewardSendContext(
                usrUserId: $usrUserId,
                platform: $platform,
                rewards: $typeRewards,
                now: $now,
                sendMethod: $policy->getSendMethodByRewardType($type),
            );

            // 報酬配布の実行
            $rewardSent = $sendService->send($context);

            // 必要ならメールボックスへ送信する
            $rewardSent = $this->sendToMessage(
                usrUserId: $usrUserId,
                rewards: $rewardSent->getRewards(),
            );

            // 後処理
            $this->rewardManager->afterSend($rewardSent);

            // ログ送信
            $this->logging($usrUserId, $type, $rewardSent);

            // 報酬送信結果をまとめる
            $rewardSendSummary->addRewards($rewardSent->getRewards());
        }

        return $rewardSendSummary;
    }

    /**
     * 報酬付与前の前処理をまとめたメソッド
     */
    private function beforeSend(string $usrUserId, CarbonImmutable $now): void
    {
        $rewards = $this->rewardManager->getNeedToSendRewards();

        // 実配布用リソースへ変換
        $rewards = $this->convertRewards($usrUserId, $now, $rewards);

        $this->rewardManager->addRewards($rewards);
    }

    /**
     * 何らかの理由で報酬を即時受取できず、メールボックスへ送信する必要がある報酬を、メールボックスへ送信する
     *
     * @param Collection<BaseReward> $rewards
     * @return RewardSent
     */
    private function sendToMessage(
        string $usrUserId,
        Collection $rewards,
    ): RewardSent {
        $messageSystemRewards = $rewards->filter(function (BaseReward $reward) {
            return $reward->getUnreceivedRewardReason() === UnreceivedRewardReason::SENT_TO_MESSAGE
                && $reward->isSent() === false;
        });
        if ($messageSystemRewards->isEmpty()) {
            return new RewardSent($rewards);
        }

        // メッセージ送信
        // 循環参照を避けるためにコンストラクタインジェクションしない
        $messageDelegator = app(MessageDelegator::class);
        foreach ($messageSystemRewards as $reward) {
            /** @var BaseReward $reward */
            $messageDelegator->addNewSystemMessage(
                usrUserId: $usrUserId,
                rewardGroupId: null,
                expiredAt: null, // 無期限
                reward: $reward,
                title: MessageConstant::REWARD_UNRECEIVED_TITLE,
                body: MessageConstant::REWARD_UNRECEIVED_BODY,
                prefixMessageSource: MessageSource::RESOURCE_LIMIT_REACHED->value,
            );

            $reward->markAsSent();
        }

        return new RewardSent($rewards);
    }

    /**
     * 実際に配布するリソースへ変換する処理をまとめたメソッド
     *
     * 例: idleBoxアイテムをcoinやitemなどのリアルリソースへ変換する処理を、報酬配布を実行する前に行う
     *
     * @param Collection<string, BaseReward> $rewards key: BaseReward.id
     * @return Collection<BaseReward>
     */
    private function convertRewards(string $usrUserId, CarbonImmutable $now, Collection $rewards): Collection
    {
        $targetRewardTypes = $rewards->mapWithKeys(fn(BaseReward $reward) => [$reward->getType() => true]);

        /**
         * 放置ボックスアイテムの仕様がオミットになっているのでコメントアウト。
         * ただし、将来的に放置ボックスアイテムの仕様が復活する可能性があるので、コメントアウトしている
         */
        // if ($targetRewardTypes->has(RewardType::ITEM->value)) {
        //     /** @var ItemDelegator $itemDelegator */
        //     $itemDelegator = app(ItemDelegator::class);
        //     $itemDelegator->convertIdleBoxToRealResources(
        //         $usrUserId,
        //         $rewards,
        //         $now,
        //     );
        // }

        if ($targetRewardTypes->has(RewardType::EMBLEM->value)) {
            /** @var EmblemDelegator $emblemDelegator */
            $emblemDelegator = app(EmblemDelegator::class);
            $emblemDelegator->convertDuplicatedEmblemToCoin(
                $usrUserId,
                $rewards,
            );
        }

        if ($targetRewardTypes->has(RewardType::UNIT->value)) {
            /** @var UnitDelegator $unitDelegator */
            $unitDelegator = app(UnitDelegator::class);
            $unitDelegator->convertDuplicatedUnitToItem(
                $usrUserId,
                $rewards,
            );
        }

        if ($targetRewardTypes->has(RewardType::ARTWORK_FRAGMENT->value)) {
            /** @var EncyclopediaDelegator $encyclopediaDelegator */
            $encyclopediaDelegator = app(EncyclopediaDelegator::class);
            $encyclopediaDelegator->addArtworkRewardWhenArtworkCompleted(
                $usrUserId,
                $rewards,
            );
        }

        if ($targetRewardTypes->has(RewardType::ARTWORK->value)) {
            /** @var EncyclopediaDelegator $encyclopediaDelegator */
            $encyclopediaDelegator = app(EncyclopediaDelegator::class);
            $encyclopediaDelegator->convertDuplicatedArtworkToCoin(
                $usrUserId,
                $rewards,
            );
        }

        return $rewards;
    }

    private function getSendService(string $type): ?RewardSendServiceInterface
    {
        $sendServiceClass = $this->sendServiceClasses[$type] ?? null;

        if ($sendServiceClass === null) {
            return null;
        }

        $sendServiceClass = app($sendServiceClass);

        if ($sendServiceClass instanceof RewardSendServiceInterface === false) {
            return null;
        }

        return $sendServiceClass;
    }

    private function getLogRepository(string $type): ?ILogModelRepositoryRewardSend
    {
        $logRepositoryClass = $this->logRepositoryClasses[$type] ?? null;

        if ($logRepositoryClass === null) {
            return null;
        }

        $repository = app($logRepositoryClass);

        if ($repository instanceof ILogModelRepositoryRewardSend === false) {
            return null;
        }

        return $repository;
    }

    /**
     * 送信済み報酬情報を元に、報酬タイプごとにログを送信する
     */
    private function logging(string $usrUserId, string $type, RewardSent $sentData): void
    {
        $logRepository = $this->getLogRepository($type);
        if ($logRepository === null) {
            return;
        }

        foreach ($sentData->getRewards() as $reward) {
            if ($reward->isUnreceived()) {
                // 未受取報酬はログに記録しない
                // 例：即時配布せずにメールボックスへ送信された場合は、報酬配布されていないのでログに記録してはいけない
                continue;
            }

            /** @var BaseReward $reward */
            $logRepository->createByReward(
                $usrUserId,
                $reward,
            );
        }
    }

    /**
     * 指定した報酬クラスの報酬を配布用リソースへ変換したものを取得する
     *
     * 配布は実行しないが、配布リソースは何になるかを知る必要がある際に使用する
     * 例: チュートリアルガシャ。引き直しができるため、配布実行する前に、実際に何を配布するかをレスポンスする必要がある。
     *
     * @param Collection<BaseReward> $rewards
     * @return Collection<BaseReward>
     */
    public function getConvertedRewardsWithoutSend(
        string $usrUserId,
        CarbonImmutable $now,
        Collection $rewards,
    ): Collection {
        return $this->convertRewards($usrUserId, $now, $rewards);
    }
}
