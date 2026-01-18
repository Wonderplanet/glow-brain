<?php

declare(strict_types=1);

namespace App\Domain\Message\Repositories;

use App\Domain\Message\Models\Eloquent\UsrMessage as EloquentUsrMessage;
use App\Domain\Message\Models\UsrMessage;
use App\Domain\Message\Models\UsrMessageInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRawRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UsrMessageRepository extends UsrModelMultiCacheRawRepository
{
    protected string $modelClass = UsrMessage::class;

    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrMessageInterface $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mng_message_id' => $model->getMngMessageId(),
                'message_source' => $model->getMessageSource(),
                'reward_group_id' => $model->getRewardGroupId(),
                'resource_type' => $model->getResourceType(),
                'resource_id' => $model->getResourceId(),
                'resource_amount' => $model->getResourceAmount(),
                'title' => $model->getTitle(),
                'body' => $model->getBody(),
                'opened_at' => $model->getOpenedAt(),
                'received_at' => $model->getReceivedAt(),
                'is_received' => $model->getIsReceived(),
                'expired_at' => $model->getExpiredAt(),
            ];
        })->toArray();

        EloquentUsrMessage::upsert(
            $upsertValues,
            ['id'],
            [
                'mng_message_id',
                'message_source',
                'reward_group_id',
                'resource_type',
                'resource_id',
                'resource_amount',
                'title',
                'body',
                'opened_at',
                'received_at',
                'is_received',
                'expired_at',
            ]
        );
    }

    /**
     * メッセージ単独作成
     *
     * @param string $usrUserId
     * @param string $mngMessageId
     * @param string|null $messageSource
     * @param CarbonImmutable|null $expiredAt
     * @return UsrMessageInterface
     */
    public function create(
        string $usrUserId,
        string $mngMessageId,
        ?string $messageSource = null,
        ?CarbonImmutable $expiredAt = null
    ): UsrMessageInterface {
        $usrMessage = UsrMessage::create(
            usrUserId: $usrUserId,
            mngMessageId: $mngMessageId,
            messageSource: $messageSource,
            expiredAt: $expiredAt
        );

        $this->syncModel($usrMessage);

        return $usrMessage;
    }

    /**
     * システム送信メッセージを作成する
     *
     * @param string $usrUserId
     * @param Collection<string> $messageSourceParts メッセージを受け取った経緯情報を要素にもつ配列
     * @param CarbonImmutable|null $expiredAt
     * @param string $resourceType
     * @param string|null $resourceId
     * @param int $resourceAmount
     * @param string $title
     * @param string $body
     * @return UsrMessageInterface
     */
    public function createSystemMessage(
        string $usrUserId,
        Collection $messageSourceParts,
        ?string $rewardGroupId,
        ?CarbonImmutable $expiredAt,
        string $resourceType,
        ?string $resourceId,
        int $resourceAmount,
        string $title,
        string $body,
    ): UsrMessageInterface {
        $combinedMessageSource = UsrMessage::makeCombinedMessageSource($messageSourceParts);

        $usrMessage = UsrMessage::create(
            usrUserId: $usrUserId,
            mngMessageId: null,
            messageSource: $combinedMessageSource,
            rewardGroupId: $rewardGroupId,
            expiredAt: $expiredAt,
            resourceType: $resourceType,
            resourceId: $resourceId,
            resourceAmount: $resourceAmount,
            title: $title,
            body: $body
        );
        $this->syncModel($usrMessage);
        return $usrMessage;
    }

    /**
     * @param string $userId
     * @return Collection
     */
    public function getByUserId(string $userId): Collection
    {
        return $this->cachedGetAll($userId);
    }

    /**
     * 対象ユーザーの登録済みのメッセージidを配列で取得
     *
     * @param string $userId
     * @return Collection
     */
    public function getRegisteredByUserId(string $userId): Collection
    {
        return $this->getByUserId($userId)
            ->filter(fn(UsrMessage $row) => !is_null($row->getMngMessageId()))
            ->keyBy(function (UsrMessage $row): string {
                return $row->getMngMessageId();
            });
    }

    /**
     * 受け取り可能なメッセージを取得
     *
     * @param string $userId
     * @param CarbonImmutable $nowAt
     * @return Collection
     */
    public function getReceivableList(string $userId, CarbonImmutable $nowAt): Collection
    {
        return $this->cachedGetMany(
            $userId,
            cacheCallback: function (Collection $cache) use ($nowAt) {
                return $cache->filter(function (UsrMessageInterface $model) use ($nowAt) {
                    $expiredAt = CarbonImmutable::parse($model->getExpiredAt());
                    return $expiredAt >= $nowAt;
                });
            },
            expectedCount: null,
            dbCallback: function () use ($userId, $nowAt) {
                return UsrMessage::query()
                    ->where('usr_user_id', $userId)
                    ->where(function ($query) use ($nowAt) {
                        $query
                            ->whereNull('expired_at')
                            ->orWhere('expired_at', '>=', $nowAt);
                    })
                    ->get()
                    ->map(fn($record) => UsrMessage::createFromRecord($record));
            }
        );
    }

    /**
     * 指定したuserIdとmessageIdsのデータを取得
     *
     * @param string $userId
     * @param array<int, string> $mngMessageIds
     * @return Collection
     */
    public function getByUserIdAndMessageIds(string $userId, array $mngMessageIds): Collection
    {
        if (count($mngMessageIds) === 0) {
            return collect();
        }
        $mngMessageIds = array_fill_keys($mngMessageIds, true);

        return $this->cachedGetMany(
            $userId,
            cacheCallback: function (Collection $cache) use ($mngMessageIds) {
                return $cache->filter(function (UsrMessageInterface $model) use ($mngMessageIds) {
                    return isset($mngMessageIds[$model->getMngMessageId()]);
                });
            },
            expectedCount: count($mngMessageIds),
            dbCallback: function () use ($userId, $mngMessageIds) {
                return UsrMessage::query()
                    ->where('usr_user_id', $userId)
                    ->whereIn('mng_message_id', array_keys($mngMessageIds))
                    ->get()
                    ->map(fn($record) => UsrMessage::createFromRecord($record));
            }
        );
    }
    /**
     * 未読メッセージの数を取得
     *
     * @param string $userId
     * @param CarbonImmutable $nowAt
     * @return Collection
     */
    public function getUnopenedMessages(string $userId, CarbonImmutable $nowAt): Collection
    {
        return $this->cachedGetMany(
            $userId,
            cacheCallback: function (Collection $cache) use ($nowAt) {
                return $cache->filter(function (UsrMessageInterface $model) use ($nowAt) {
                    $expiredAt = CarbonImmutable::parse($model->getExpiredAt());
                    return $expiredAt >= $nowAt && is_null($model->getOpenedAt()) && is_null($model->getReceivedAt());
                });
            },
            expectedCount: null,
            dbCallback: function () use ($userId, $nowAt) {
                return UsrMessage::query()
                    ->where('usr_user_id', $userId)
                    ->whereNull('opened_at')
                    ->whereNull('received_at')
                    ->where(function ($query) use ($nowAt) {
                        $query
                            ->whereNull('expired_at')
                            ->orWhere('expired_at', '>=', $nowAt);
                    })
                    ->get()
                    ->map(fn($record) => UsrMessage::createFromRecord($record));
            }
        );
    }
    public function getByIds(string $userId, Collection $usrMessageIds): Collection
    {
        if ($usrMessageIds->isEmpty()) {
            return collect();
        }
        $targetIds = array_fill_keys($usrMessageIds->all(), true);

        return $this->cachedGetMany(
            $userId,
            cacheCallback: function (Collection $cache) use ($targetIds) {
                return $cache->filter(function (UsrMessageInterface $model) use ($targetIds) {
                    return isset($targetIds[$model->getId()]);
                });
            },
            expectedCount: count($targetIds),
            dbCallback: function () use ($userId, $targetIds) {
                return UsrMessage::query()
                    ->whereIn('id', array_keys($targetIds))
                    ->where('usr_user_id', $userId)
                    ->get()
                    ->map(fn($record) => UsrMessage::createFromRecord($record));
            },
        );
    }

    /**
     * 同一のRewardGroupIdが設定されているメッセージを取得
     *
     * @param string $userId
     * @param Collection $rewardGroupIds
     * @return Collection
     */
    public function getByRewardGroupIds(string $userId, Collection $rewardGroupIds): ?Collection
    {
        if ($rewardGroupIds->isEmpty()) {
            return collect();
        }
        $targetIds = array_fill_keys($rewardGroupIds->all(), true);
        return $this->cachedGetMany(
            $userId,
            cacheCallback: function (Collection $cache) use ($targetIds) {
                return $cache->filter(function (UsrMessageInterface $model) use ($targetIds) {
                    return isset($targetIds[$model->getRewardGroupId()]);
                });
            },
            expectedCount: null,
            dbCallback: function () use ($userId, $targetIds) {
                return UsrMessage::query()
                    ->whereIn('reward_group_id', array_keys($targetIds))
                    ->where('usr_user_id', $userId)
                    ->get()
                    ->map(fn($record) => UsrMessage::createFromRecord($record));
            },
        );
    }
}
