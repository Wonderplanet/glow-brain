<?php

declare(strict_types=1);

namespace App\Domain\Message\Services;

use App\Domain\AdventBattle\Delegators\AdventBattleDelegator;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Utils\StringUtil;
use App\Domain\JumpPlus\Delegators\JumpPlusDelegator;
use App\Domain\Message\Constants\MessageConstant;
use App\Domain\Message\Entities\Message;
use App\Domain\Message\Enums\MessageSource;
use App\Domain\Message\Enums\MngMessageType;
use App\Domain\Message\Models\UsrMessageInterface;
use App\Domain\Message\Models\UsrTemporaryIndividualMessageInterface;
use App\Domain\Message\Repositories\LogSystemMessageAdditionRepository;
use App\Domain\Message\Repositories\UsrMessageRepository;
use App\Domain\Message\Repositories\UsrTemporaryIndividualMessageRepository;
use App\Domain\Pvp\Delegators\PvpDelegator;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Entities\Rewards\MessageReward;
use App\Domain\Resource\Mng\Entities\MngMessageBundle;
use App\Domain\Resource\Mng\Entities\MngMessageEntity;
use App\Domain\Resource\Mng\Entities\MngMessageRewardEntity;
use App\Domain\Resource\Mng\Repositories\MngMessageBundleRepository;
use App\Domain\Resource\Usr\Entities\UsrUserLoginEntity;
use App\Domain\User\Delegators\UserDelegator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UsrMessageService
{
    public function __construct(
        private UsrMessageRepository $usrMessageRepository,
        private MngMessageBundleRepository $mngMessageBundleRepository,
        private UsrTemporaryIndividualMessageRepository $usrTemporaryIndividualMessageRepository,
        private LogSystemMessageAdditionRepository $logSystemMessageAdditionRepository,
        // Delegators
        private AdventBattleDelegator $adventBattleDelegator,
        private UserDelegator $userDelegator,
        private JumpPlusDelegator $jumpPlusDelegator,
        private PvpDelegator $pvpDelegator,
    ) {
    }

    /**
     * 新規メッセージ登録
     *
     * @param string $usrUserId
     * @param string $language
     * @param CarbonImmutable $now
     * @param CarbonImmutable $gameStartAt
     * @return int
     * @throws GameException
     */
    public function addNewMessages(
        string $usrUserId,
        CarbonImmutable $now,
        string $language,
        CarbonImmutable $gameStartAt
    ): int {
        // ユーザー情報取得
        $usrUserLogin = $this->userDelegator->getUsrUserLogin($usrUserId);
        if (is_null($usrUserLogin)) {
            throw new GameException(ErrorCode::USER_NOT_FOUND);
        }

        // 全体配布の登録可能なメッセージを取得
        $mngMessageBundles = $this->mngMessageBundleRepository->getActiveMngMessageBundlesByLanguage($language, $now);
        $mngMessages = $mngMessageBundles->map(function (MngMessageBundle $bundle) {
            return $bundle->getMngMessage();
        })->values();

        // 登録可能なメッセージのうち、ユーザーに登録済みのメッセージIdを取得
        $registeredUsrMessages = $this->usrMessageRepository->getRegisteredByUserId($usrUserId);

        // 全体配布の未登録メッセージを登録
        $addableMngMessagesByTypeAll
            = $this->getAddableMessagesByTypeAll($usrUserLogin, $mngMessages, $registeredUsrMessages, $gameStartAt);

        // 個別配布の未登録メッセージを登録
        $addableMngMessagesByTypeIndividual
            = $this->getAddableMessagesByTypeIndividual($usrUserId, $mngMessages, $registeredUsrMessages);

        // 受け取り期限日時用に生成
        $expiredBaseAt = $now;

        // 全体配布の未登録メッセージを登録
        $addMessages = collect();
        /** @var MngMessageEntity $entityTypeAll */
        foreach ($addableMngMessagesByTypeAll as $entityTypeAll) {
            // 受け取り期限日時を算出
            $expiredAt = $expiredBaseAt->addDays($entityTypeAll->getAddExpiredDays());

            $usrMessage = $this->usrMessageRepository
                ->create($usrUserId, $entityTypeAll->getId(), MessageSource::MNG_MESSAGE->value, $expiredAt);
            $addMessages->push($usrMessage);
        }

        // 個別配布の未登録メッセージを登録する
        /** @var MngMessageEntity $entityTypeIndividual */
        foreach ($addableMngMessagesByTypeIndividual as $entityTypeIndividual) {
            // 受け取り期限日時を算出
            $expiredAt = $expiredBaseAt->addDays($entityTypeIndividual->getAddExpiredDays());

            $usrMessage = $this->usrMessageRepository
                ->create($usrUserId, $entityTypeIndividual->getId(), MessageSource::MNG_MESSAGE->value, $expiredAt);
            $addMessages->push($usrMessage);
        }

        // 受け取り可能な降臨バトルの報酬があれば受け取る
        $adventBattleRewards = $this->adventBattleDelegator->getReceivableRewards($usrUserId, $now);
        foreach ($adventBattleRewards as $adventBattleReward) {
            /** @var \App\Domain\Resource\Entities\Rewards\AdventBattleReward $adventBattleReward */
            // 受け取り期限日時を算出
            $expiredAt = $expiredBaseAt->addDays(MessageConstant::ADVENT_BATTLE_REWARD_MESSAGE_EXPIRATION_DAYS);
            $usrMessage = $this->addNewSystemMessage(
                $usrUserId,
                $adventBattleReward->getMstAdventBattleRewardGroupId(),
                $expiredAt,
                $adventBattleReward,
                $adventBattleReward->getTitle(),
                $adventBattleReward->getBody(),
            );
            $addMessages->push($usrMessage);
        }

        // 前回開催シーズンより前のPvPシーズン報酬をメッセージに付与
        $pvpOldSeasonRewardsGroup = $this->pvpDelegator->getOldSeasonRewards(
            $usrUserId,
            $now,
        );

        // PvPの前回開催シーズンより前のシーズンの報酬がなければ処理終了
        if ($pvpOldSeasonRewardsGroup->isEmpty()) {
            return $addMessages->count();
        }

        $markSysPvpSeasonIds = collect();
        foreach ($pvpOldSeasonRewardsGroup as $sysPvpSeasonId => $pvpOldSeasonRewards) {
            // 受取済みフラグを更新するためにIDを保持
            $markSysPvpSeasonIds->push($sysPvpSeasonId);

            // PvPシーズン報酬をメッセージに登録
            foreach ($pvpOldSeasonRewards as $pvpOldSeasonReward) {
                /** @var \App\Domain\Resource\Entities\Rewards\PvpReward $pvpOldSeasonReward */
                $expiredAt = $expiredBaseAt->addDays($pvpOldSeasonReward->getExpirationDays());
                $usrMessage = $this->addNewSystemMessage(
                    $usrUserId,
                    $pvpOldSeasonReward->getRewardGroupId(),
                    $expiredAt,
                    $pvpOldSeasonReward,
                    $pvpOldSeasonReward->getTitle(),
                    $pvpOldSeasonReward->getBody(),
                );
                $addMessages->push($usrMessage);
            }
        }
        $this->pvpDelegator->markSeasonRewardAsReceivedBySeasonIds($usrUserId, $markSysPvpSeasonIds);

        return $addMessages->count();
    }

    /**
     * 受取可のジャンプ+連携報酬をメッセージに登録
     * @param string $usrUserId
     * @param \Carbon\CarbonImmutable $now
     * @return \Illuminate\Support\Collection<\App\Domain\Message\Models\UsrMessageInterface>
     */
    public function addUsrMessagesForJumpPlusReward(
        string $usrUserId,
        CarbonImmutable $now,
    ): Collection {
        $usrMessages = collect();

        $jumpPlusRewardBundles = $this->jumpPlusDelegator->getReceivableRewards($usrUserId, $now);
        if ($jumpPlusRewardBundles->isEmpty()) {
            return $usrMessages;
        }

        // メッセージ登録
        $jumpPlusRewards = $jumpPlusRewardBundles->flatMap->getJumpPlusRewards();
        foreach ($jumpPlusRewards as $jumpPlusReward) {
            /** @var \App\Domain\Resource\Entities\Rewards\JumpPlusReward $jumpPlusReward */
            $usrMessage = $this->addNewSystemMessage(
                $usrUserId,
                $jumpPlusReward->getMngJumpPlusRewardScheduleId(),
                CarbonImmutable::parse($jumpPlusReward->getReceiveExpireAt()),
                $jumpPlusReward,
                MessageConstant::JUMP_PLUS_TITLE,
                MessageConstant::JUMP_PLUS_BODY,
            );
            $usrMessages->push($usrMessage);
        }

        // メッセージ登録してから、受取済ステータスへ更新する
        $this->jumpPlusDelegator->markRewardsAsReceived(
            $usrUserId,
            $jumpPlusRewardBundles->map->getDynJumpPlusReward(),
        );

        return $usrMessages;
    }

    public function getAddableMessageCount(
        string $usrUserId,
        CarbonImmutable $now,
        string $language,
        CarbonImmutable $gameStartAt
    ): int {
        // ユーザー情報取得
        $usrUserLogin = $this->userDelegator->getUsrUserLogin($usrUserId);
        if (is_null($usrUserLogin)) {
            return 0;
        }

        // 全体配布の登録可能なメッセージを取得
        $mngMessageBundles = $this->mngMessageBundleRepository->getActiveMngMessageBundlesByLanguage($language, $now);
        $mngMessages = $mngMessageBundles->map(function (MngMessageBundle $bundle) {
            return $bundle->getMngMessage();
        })->values();

        // 登録可能なメッセージのうち、ユーザーに登録済みのメッセージIdを取得
        $registeredUsrMessages = $this->usrMessageRepository->getRegisteredByUserId($usrUserId);

        // 全体配布の未登録メッセージを取得
        $addableMngMessagesByTypeAll
            = $this->getAddableMessagesByTypeAll($usrUserLogin, $mngMessages, $registeredUsrMessages, $gameStartAt);

        // 個別配布の未登録メッセージを取得
        $addableMngMessagesByTypeIndividual
            = $this->getAddableMessagesByTypeIndividual($usrUserId, $mngMessages, $registeredUsrMessages);

        $addableMessageCount = $addableMngMessagesByTypeAll->merge($addableMngMessagesByTypeIndividual);

        return $addableMessageCount->count();
    }

    private function getAddableMessagesByTypeIndividual(
        string $usrUserId,
        Collection $mngMessages,
        Collection $registeredUsrMessages
    ): Collection {
        // 個別配布の登録可能なメッセージを取得
        $mngMessagesByTypeIndividual = $mngMessages
            ->where(fn(MngMessageEntity $entity) => $entity->getType() === MngMessageType::INDIVIDUAL->value);
        $mngMessageTypeIndividualIds = $mngMessagesByTypeIndividual
            ->map(fn(MngMessageEntity $entity) => $entity->getId())
            ->toArray();
        $usrTemporaryIndividualMessageMngMessageIds = $this->usrTemporaryIndividualMessageRepository
            ->getByUserIdAndMngMessageIds($usrUserId, $mngMessageTypeIndividualIds)
            ->map(fn(UsrTemporaryIndividualMessageInterface $row) => $row->getMngMessageId())
            ->toArray();
        // 個別配布メッセージのうち、ユーザーが配布対象であるメッセージを取得
        $targetMngMessages = $mngMessagesByTypeIndividual
            ->where(function (MngMessageEntity $entity) use ($usrTemporaryIndividualMessageMngMessageIds) {
                return in_array($entity->getId(), $usrTemporaryIndividualMessageMngMessageIds, true);
            });
        $addableMngMessages = collect();
        // 個別配布の未登録メッセージを登録する
        /** @var MngMessageEntity $entityTypeIndividual */
        foreach ($targetMngMessages as $entityTypeIndividual) {
            if (isset($registeredUsrMessages[$entityTypeIndividual->getId()])) {
                // 登録済みの場合は次のメッセージへ
                continue;
            }
            $addableMngMessages->push($entityTypeIndividual);
        }
        return $addableMngMessages;
    }

    private function getAddableMessagesByTypeAll(
        UsrUserLoginEntity $usrUserLogin,
        Collection $mngMessages,
        Collection $registeredUsrMessages,
        CarbonImmutable $gameStartAt
    ): Collection {
        $mngMessagesByTypeAll = $mngMessages
            ->where(fn(MngMessageEntity $entity) => $entity->getType() === MngMessageType::ALL->value);
        $addableMngMessages = collect();

        /** @var MngMessageEntity $entityTypeAll */
        foreach ($mngMessagesByTypeAll as $entityTypeAll) {
            if (isset($registeredUsrMessages[$entityTypeAll->getId()])) {
                // 登録済みの場合は次のメッセージへ
                continue;
            }

            $accountCreatedStartAt = CarbonImmutable::parse($entityTypeAll->getAccountCreatedStartAt());
            $accountCreatedEndAt = CarbonImmutable::parse($entityTypeAll->getAccountCreatedEndAt());
            // 開始日がnullでなければ開始日を判定
            if (
                (!is_null($entityTypeAll->getAccountCreatedStartAt()) && $gameStartAt < $accountCreatedStartAt)
                || (!is_null($entityTypeAll->getAccountCreatedEndAt()) && $gameStartAt > $accountCreatedEndAt)
            ) {
                // ユーザー作成日時が条件の開始日より過去か終了日より未来なら対象外なので次のループへ
                continue;
            }

            $addableMngMessages->push($entityTypeAll);
        }
        return $addableMngMessages;
    }

    /**
     * システムメッセージの追加
     * @param string|null $prefixMessageSource usr_messages.message_sourceの先頭に追加する経緯情報
     * @return UsrMessageInterface
     */
    public function addNewSystemMessage(
        string $usrUserId,
        ?string $rewardGroupId,
        ?CarbonImmutable $expiredAt,
        BaseReward $reward,
        string $title,
        string $body,
        ?string $prefixMessageSource = null,
    ): UsrMessageInterface {
        // logTriggerDtoからtriggerSourceとtriggerValueを取得
        $logTriggerDto = $reward->getLogTriggerData();

        // messageSourcePartsを構築
        $messageSourceParts = collect();
        if ($prefixMessageSource !== null) {
            $messageSourceParts->push($prefixMessageSource);
        }
        $messageSourceParts->push($logTriggerDto->getTriggerSource());
        $messageSourceParts->push($logTriggerDto->getTriggerValue());

        $usrMessage = $this->usrMessageRepository->createSystemMessage(
            $usrUserId,
            $messageSourceParts,
            $rewardGroupId,
            $expiredAt,
            $reward->getType(),
            $reward->getResourceId(),
            $reward->getAmount(),
            $title,
            $body,
        );

        // システムメッセージの追加ログを登録
        $this->logSystemMessageAdditionRepository->create($usrUserId, $reward);

        return $usrMessage;
    }

    /**
     * 未読・未登録メッセージカウントの取得
     *
     * @param string $usrUserId
     * @param string $language
     * @param CarbonImmutable $now
     * @param CarbonImmutable $gameStartAt
     * @return int
     */
    public function getUnopenedMessageCount(
        string $usrUserId,
        CarbonImmutable $now,
        string $language,
        CarbonImmutable $gameStartAt
    ): int {
        $unopenedMessageCount = $this->usrMessageRepository->getUnopenedMessages($usrUserId, $now)->count();
        $unregisteredMessageCount = $this->getAddableMessageCount($usrUserId, $now, $language, $gameStartAt);
        return $unopenedMessageCount + $unregisteredMessageCount;
    }

    /**
     * @param string $usrUserId
     * @param Collection $usrMessageIds
     * @return Collection<UsrMessageInterface>
     * @throws GameException
     */
    public function validateMessageIds(string $usrUserId, Collection $usrMessageIds): Collection
    {
        if ($usrMessageIds->isEmpty()) {
            throw new GameException(ErrorCode::INVALID_PARAMETER, 'messageIds is empty.');
        }
        $usrMessages = $this->usrMessageRepository->getByIds($usrUserId, $usrMessageIds);
        if ($usrMessages->count() !== $usrMessageIds->count()) {
            throw new GameException(ErrorCode::INVALID_PARAMETER, 'not found usr_message_id');
        }

        return $usrMessages;
    }

    /**
     * メッセージデータを取得
     * メッセージ一覧のレスポンスで必要な情報をまとめたクラスインスタンスを作って返す
     *
     * @return Collection<\App\Domain\Message\Entities\Message>
     */
    public function getMessageData(string $usrUserId, CarbonImmutable $now, string $language): Collection
    {
        $usrMessages = $this->usrMessageRepository->getReceivableList($usrUserId, $now);

        [$systemUsrMessages, $usrMessages] = $usrMessages->partition(function (UsrMessageInterface $row) {
            return is_null($row->getMngMessageId());
        });

        // MngMessageIdが有効なものだけ取得
        $resultDataCollection = $this->getMngMessageData($usrMessages, $language, $now);
        $resultDataCollection = $resultDataCollection->merge($this->getSystemMessageData($systemUsrMessages, $now));

        // 配布開始日時の降順でソートして返す
        //  日時が同じ場合はmngMessage.idの昇順でソートする
        $resultDataCollection = $resultDataCollection
            ->sort(function (Message $row1, Message $row2) {
                $row1Carbon = CarbonImmutable::parse($row1->getStartAt());
                $row2Carbon = CarbonImmutable::parse($row2->getStartAt());
                $dateComparison = $row2Carbon->timestamp - $row1Carbon->timestamp;
                if ($dateComparison !== 0) {
                    return $dateComparison;
                }

                // 日時が同じ場合はIDで比較
                // 文字列による比較になるが、配信日時の降順で並んでいるなら問題ないので
                // 順序を固定するためにこの条件を追加している
                return $row1->getMngMessageId() <=> $row2->getMngMessageId();
            });
        return $resultDataCollection;
    }

    /**
     * MngMessageのデータを取得し、配布アイテムのデータを整形してMessageクラスのインスタンスを作成する
     * @param Collection<\App\Domain\Message\Models\UsrMessageInterface> $usrMessages
     * @return Collection<\App\Domain\Message\Entities\Message>
     */
    private function getMngMessageData(Collection $usrMessages, string $language, CarbonImmutable $now): Collection
    {
        if ($usrMessages->isEmpty()) {
            // 表示できるメッセージがなければ空を返す
            return collect();
        }

        $mngMessageIds = $usrMessages->map(function (UsrMessageInterface $row) {
            return $row->getMngMessageId();
        })->values();

        // MngMessageServiceを使ってBundleデータを取得（期間チェックはしない）
        $mngMessageBundles = $this->mngMessageBundleRepository
            ->getMngMessageBundlesByLanguageAndMngMessageIds($language, $mngMessageIds, $now);

        $messages = collect();
        /** @var \App\Domain\Message\Models\UsrMessageInterface $usrMessage */
        foreach ($usrMessages as $usrMessage) {
            $mngMessageId = $usrMessage->getMngMessageId();

            $mngMessageBundle = $mngMessageBundles->get($mngMessageId);
            if (is_null($mngMessageBundle)) {
                continue;
            }
            /** @var MngMessageBundle $mngMessageBundle */

            $mngMessage = $mngMessageBundle->getMngMessage();
            $mngMessageI18n = $mngMessageBundle->getMngMessageI18n();

            // 配布アイテムのデータを整形
            $messageRewards = collect();
            $mngMessageRewards = $mngMessageBundle->getMngMessageRewards();
            /** @var MngMessageRewardEntity $messageReward */
            foreach ($mngMessageRewards as $messageReward) {
                $messageReward = new MessageReward(
                    $messageReward->getResourceType(),
                    $messageReward->getResourceId(),
                    $messageReward->getResourceAmount(),
                    MessageSource::MNG_MESSAGE->value,
                    $mngMessageId,
                );

                $messageRewards->push($messageReward);
            }

            $messageData = new Message(
                $mngMessage->getId(),
                $mngMessage->getStartAt(),
                $mngMessageI18n->getTitle(),
                $mngMessageI18n->getBody(),
                $usrMessage,
                $messageRewards
            );

            $messages->push($messageData);
        }
        return $messages;
    }

    /**
     * 報酬情報などを用意(配布なし)しつつ、システムメッセージのデータをまとめたレスポンスで使うDataクラスインスタンスを用意する
     * @return Collection<\App\Domain\Message\Entities\Message>
     */
    private function getSystemMessageData(Collection $usrMessages, CarbonImmutable $now): Collection
    {
        if ($usrMessages->isEmpty()) {
            // 表示できるメッセージがなければ空を返す
            return collect();
        }

        // GroupIdごとに報酬をまとめる
        $rewardList = [];
        foreach ($usrMessages as $usrMessage) {
            $rewardGroupId = $usrMessage->getRewardGroupId();
            if (is_null($rewardGroupId)) {
                continue;
            }
            if (!isset($rewardList[$rewardGroupId])) {
                $rewardList[$rewardGroupId] = [];
            }
            $rewardList[$rewardGroupId][] =
                new MessageReward(
                    $usrMessage->getResourceType(),
                    $usrMessage->getResourceId(),
                    $usrMessage->getResourceAmount(),
                    $usrMessage->getMessageSource(),
                    null
                );
        }

        $resultDataCollection = collect();
        $rewardGroupExistsList = [];
        /** @var \App\Domain\Message\Models\UsrMessageInterface $usrMessage */
        foreach ($usrMessages as $usrMessage) {
            if (isset($rewardGroupExistsList[$usrMessage->getRewardGroupId()])) {
                // すでに登録済みのRewardGroupIdはスキップ
                continue;
            }

            $messageRewardData = collect();
            // RewardGroupIdが設定されていればデータ取得してリワードデータをマージする
            if (!is_null($usrMessage->getRewardGroupId())) {
                foreach ($rewardList[$usrMessage->getRewardGroupId()] as $reward) {
                    $messageRewardData->push($reward);
                }
                // 重複を避けるため、RewardGroupIdの存在を記録しておく
                $rewardGroupExistsList[$usrMessage->getRewardGroupId()] = true;
            } else {
                // RewardGroupIdが設定されていない場合は、UsrMessageの報酬情報をそのまま使用する
                $messageRewardData->push(
                    new MessageReward(
                        $usrMessage->getResourceType(),
                        $usrMessage->getResourceId(),
                        $usrMessage->getResourceAmount(),
                        $usrMessage->getMessageSource(),
                        null,
                    )
                );
            }

            $messageData = new Message(
                $usrMessage->getMngMessageId(),
                $usrMessage->getCreatedAt() ?? $now->toDateTimeString(),
                $usrMessage->getTitle(),
                $usrMessage->getBody(),
                $usrMessage,
                $messageRewardData,
            );

            $resultDataCollection->push($messageData);
        }
        return $resultDataCollection;
    }

    /**
     * @param Collection<UsrMessageInterface> $usrMessages
     * @return Collection<\App\Domain\Resource\Mng\Entities\MngMessageRewardEntity>
     */
    public function getMngMessageRewards(
        string $usrUserId,
        Collection $usrMessages,
        CarbonImmutable $now,
        string $language
    ): Collection {
        if ($usrMessages->isEmpty()) {
            return collect();
        }

        $mngMessageIds = collect();
        foreach ($usrMessages as $usrMessage) {
            /** @var UsrMessageInterface $usrMessage */
            if ($usrMessage->isExpired($now)) {
                // 期限切れの報酬が選択されている場合はエラー
                $msg = 'Expired MessageDistribution';
                $msg .= " mngMessageId:{$usrMessage->getMngMessageId()}";
                throw new GameException(ErrorCode::EXPIRED_MESSAGE_RESOURCE, $msg);
            }

            $mngMessageId = $usrMessage->getMngMessageId();
            if (StringUtil::isNotSpecified($mngMessageId)) {
                continue;
            }
            $mngMessageIds->put($mngMessageId, $mngMessageId);
        }
        $mngMessageIds = $mngMessageIds->values();

        // マスタデータチェック
        $mngMessageBundles = $this->mngMessageBundleRepository
            ->getMngMessageBundlesByLanguageAndMngMessageIds($language, $mngMessageIds, $now);

        if ($mngMessageBundles->count() !== $mngMessageIds->count()) {
            // マスターデータと一致しない場合はエラー
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'Not Found MngMessages. invalidMngMessageIds:%s',
                    json_encode(
                        $mngMessageIds->diff($mngMessageBundles->keys())->toArray(),
                        JSON_THROW_ON_ERROR,
                    ),
                )
            );
        }

        return $mngMessageBundles->flatMap(function (MngMessageBundle $bundle) {
            return $bundle->getMngMessageRewards();
        });
    }


    /**
     * getByIdsとRewardGroupIdがついているメッセージをマージして取得
     *
     * @param string $userId
     * @param Collection $usrMessageIds
     * @return Collection
     * @throws GameException
     */
    public function getByIdPlusRewardGroupIdData(
        string $userId,
        Collection $usrMessageIds,
        bool $isThrowError = true,
    ): ?Collection {
        if ($usrMessageIds->isEmpty()) {
            throw new GameException(ErrorCode::INVALID_PARAMETER, 'messageIds is empty.');
        }
        $usrMessages = $this->usrMessageRepository->getByIds($userId, $usrMessageIds);

        if ($usrMessages->count() !== $usrMessageIds->count()) {
            throw new GameException(ErrorCode::INVALID_PARAMETER, 'not found usr_message_id');
        }
        // rewardGroupIdを抽出
        $rewardGroupIds = $usrMessages->map(function (UsrMessageInterface $usrMessage) {
            return $usrMessage->getRewardGroupId();
        });
        // nullを除外してユニークな値を取得
        $rewardGroupIds = $rewardGroupIds->filter(function ($value) {
            return !is_null($value);
        })->unique()->values();

        // rewardGroupIdを持つメッセージを取得
        $rewardGroupMessages = $this->usrMessageRepository->getByRewardGroupIds($userId, $rewardGroupIds);
        $usrMessages = $usrMessages->merge($rewardGroupMessages);

        // エラー通知フラグがtrueの場合チェックしてエラーを投げる
        if ($isThrowError) {
            foreach ($usrMessages as $usrMessage) {
                if ($usrMessage->getIsReceived()) {
                    throw new GameException(
                        ErrorCode::INVALID_PARAMETER,
                        'Contains a message that has already been received.' . ' usrMessageId:' . $usrMessage->getId(),
                    );
                }
            }
        }

        return $usrMessages->unique(function (UsrMessageInterface $usrMessage) {
            return $usrMessage->getId();
        });
    }
}
