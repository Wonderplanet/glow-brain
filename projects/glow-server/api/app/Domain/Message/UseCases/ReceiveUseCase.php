<?php

declare(strict_types=1);

namespace App\Domain\Message\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Message\Enums\MessageSource;
use App\Domain\Message\Models\UsrMessageInterface;
use App\Domain\Message\Repositories\LogReceiveMessageRewardRepository;
use App\Domain\Message\Repositories\UsrMessageRepository;
use App\Domain\Message\Services\UsrMessageService;
use App\Domain\Resource\Entities\Rewards\MessageReward;
use App\Domain\Resource\Entities\Rewards\UserLevelUpReward;
use App\Domain\Resource\Entities\RewardSendPolicy;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Shop\Delegators\ShopDelegator;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\Data\UserLevelUpData;
use App\Http\Responses\ResultData\MessageReceiveResultData;

class ReceiveUseCase
{
    use UseCaseTrait;

    public function __construct(
        private UsrMessageRepository $usrMessageRepository,
        private LogReceiveMessageRewardRepository $logReceiveMessageRewardRepository,
        private UsrMessageService $usrMessageService,
        private UsrModelDiffGetService $usrModelDiffGetService,
        private UserDelegator $userDelegator,
        private RewardDelegator $rewardDelegator,
        private ShopDelegator $shopDelegator,
        private Clock $clock,
    ) {
    }

    /**
     * @param CurrentUser $user
     * @param int $platform
     * @param array<string> $usrMessageIds
     * @return MessageReceiveResultData
     * @throws \Throwable
     */
    public function exec(
        CurrentUser $user,
        int $platform,
        array $usrMessageIds,
        string $language,
    ): MessageReceiveResultData {
        $now = $this->clock->now();
        $usrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($user->id);
        $beforeExp = $usrUserParameter->getExp();
        $beforeLevel = $usrUserParameter->getLevel();

        $usrMessages = $this->usrMessageService->getByIdPlusRewardGroupIdData(
            $user->id,
            collect($usrMessageIds)
        );

        // システム送信メッセージと通常のメッセージに分ける
        [$systemUsrMessages, $normalUsrMessages] = $usrMessages->partition(function (UsrMessageInterface $row) {
            return is_null($row->getMngMessageId());
        });

        // 通常のメッセージの報酬を取得
        $mngMessageRewards = $this->usrMessageService->getMngMessageRewards(
            $user->id,
            $normalUsrMessages,
            $now,
            $language,
        );

        // ログ出力用に受け取り期限を収集
        $mngMessageExpiredAt = $normalUsrMessages->mapWithKeys(function (UsrMessageInterface $usrMessage) {
            return [$usrMessage->getMngMessageId() => $usrMessage->getExpiredAt()];
        });

        $addRewards = collect();
        // 報酬配布リストに追加
        try {
            /** @var \App\Domain\Resource\Mng\Entities\MngMessageRewardEntity $entity */
            foreach ($mngMessageRewards as $entity) {
                $reward = new MessageReward(
                    $entity->getResourceType(),
                    $entity->getResourceId(),
                    $entity->getResourceAmount(),
                    MessageSource::MNG_MESSAGE->value,
                    $entity->getMngMessageId(),
                    $mngMessageExpiredAt->get($entity->getMngMessageId(), null)
                );
                $this->rewardDelegator->addReward($reward);
                $addRewards->push($reward);
            }
            // システムメッセージ報酬
            /** @var \App\Domain\Message\Models\UsrMessageInterface $usrMessage */
            foreach ($systemUsrMessages as $usrMessage) {
                $reward = new MessageReward(
                    $usrMessage->getResourceType(),
                    $usrMessage->getResourceId(),
                    $usrMessage->getResourceAmount(),
                    $usrMessage->getMessageSource(),
                    $usrMessage->getMngMessageId(),
                    $usrMessage->getExpiredAt()
                );
                $this->rewardDelegator->addReward($reward);
                $addRewards->push($reward);
            }
        } catch (\Exception $e) {
            throw new GameException(ErrorCode::ERROR_RECEIVED_MESSAGE_RESOURCE, $e->getMessage());
        }

        // 受け取り日時を更新
        try {
            $updateUsrMessages = collect();
            foreach ($usrMessages as $usrMessage) {
                if (is_null($usrMessage->getOpenedAt())) {
                    // 受け取り時に既読日時がnullだった場合は、受け取り日時と同じ日時で更新する
                    $usrMessage->setOpenedAt($now);
                }
                $usrMessage->receive($now);
                $updateUsrMessages->push($usrMessage);
            }
            $this->usrMessageRepository->syncModels($updateUsrMessages);
        } catch (\Exception $e) {
            throw new GameException(ErrorCode::FAILURE_UPDATE_BY_USER_MESSAGES, $e->getMessage());
        }

        // トランザクション処理
        list(
            $usrConditionPacks,
            $afterUsrUserParameter,
            $sentMessageRewards,
        ) = $this->applyUserTransactionChanges(function () use ($user, $now, $platform, $beforeLevel) {
            // 報酬配布実行
            $this->rewardDelegator->sendRewards(
                $user->id,
                $platform,
                $now,
                RewardSendPolicy::createThrowErrorWhenResourceLimitReachedPolicy(
                    new GameException(
                        ErrorCode::MESSAGE_REWARD_BY_OVER_MAX,
                        'Cannot receive the message reward because it exceeds the resource limit.',
                    ),
                ),
            );

            $sentMessageRewards = $this->rewardDelegator->getSentRewards(MessageReward::class);

            $this->logReceiveMessageRewardRepository->createByRewards($user->id, $sentMessageRewards);

            // レベルアップパックの開放
            $usrConditionPacks = collect();
            // 報酬受け取りでレベルが上っている可能性があるので再取得
            $afterUsrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($user->id);
            if ($beforeLevel < $afterUsrUserParameter->getLevel()) {
                $usrConditionPacks = $this->shopDelegator->releaseUserLevelPack(
                    $user->id,
                    $afterUsrUserParameter->getLevel(),
                    $now
                );
            }

            return [
                $usrConditionPacks,
                $afterUsrUserParameter,
                $sentMessageRewards,
            ];
        });

        // レスポンスデータを作成
        $userLevelUpData = new UserLevelUpData(
            $beforeExp,
            $afterUsrUserParameter->getExp(),
            $this->rewardDelegator->getSentRewards(UserLevelUpReward::class),
        );

        return new MessageReceiveResultData(
            $sentMessageRewards,
            $this->makeUsrParameterData($afterUsrUserParameter),
            $this->usrModelDiffGetService->getChangedUsrUnits(),
            $this->usrModelDiffGetService->getChangedUsrItems(),
            $this->usrModelDiffGetService->getChangedUsrEmblems(),
            $userLevelUpData,
            $usrConditionPacks
        );
    }
}
