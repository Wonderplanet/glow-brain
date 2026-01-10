<?php

declare(strict_types=1);

namespace App\Domain\JumpPlus\Repositories;

use App\Domain\JumpPlus\Enums\DynJumpPlusRewardStatus;
use App\Domain\Resource\Dyn\Entities\DynJumpPlusRewardEntity;
use App\Infrastructure\DynamoDB\DynamoDbClient;
use Illuminate\Support\Collection;

class DynJumpPlusRewardRepository
{
    protected string $tableName = '';

    protected const GSI_BN_USER_ID_AND_STATUS = 'bn_user_id-status-index';

    /**
     * BatchGetItemで一度にリクエストできるアイテムの最大件数
     * @var int
     */
    protected const BATCH_GET_ITEM_MAX_REQUEST_COUNT = 100;

    public function __construct(
        private DynamoDbClient $client,
    ) {
        $this->tableName = $this->makeTableName();
    }

    protected function makeTableName(): string
    {
        return config('dynamodb.tables.jump_plus_reward.' . config('app.env'), '');
    }

    private function getNotReceivedStatusString(): string
    {
        return (string) DynJumpPlusRewardStatus::NOT_RECEIVED->value;
    }

    private function getReceivedStatusString(): string
    {
        return (string) DynJumpPlusRewardStatus::RECEIVED->value;
    }

    /**
     * 受け取り可能な報酬情報を取得する
     *
     * DynamoDBへのクエリでエラーが出た場合は、受取可報酬がないとみなして、空のコレクションを返す
     *
     * @param string $bnUserId
     * @return Collection<DynJumpPlusRewardEntity>
     */
    public function getReceivableRewards(string $bnUserId): Collection
    {
        $result = $this->client->query([
            'TableName' => $this->tableName,
            'IndexName' => self::GSI_BN_USER_ID_AND_STATUS,
            'KeyConditionExpression' => '#bn_user_id = :bn_user_id AND #status = :status',
            'ExpressionAttributeNames' => [
                '#bn_user_id' => 'bn_user_id',
                '#status' => 'status',
            ],
            'ExpressionAttributeValues' => [
                ':bn_user_id' => ['S' => $bnUserId],
                ':status' => ['N' => $this->getNotReceivedStatusString()],
            ],
        ]);

        $entities = collect();
        $items = $result['Items'] ?? [];
        foreach ($items as $item) {
            $entities->push(new DynJumpPlusRewardEntity($item));
        }

        return $entities;
    }

    /**
     * mng_jump_plus_reward_schedule_idで指定された報酬情報を取得する
     *
     * @param Collection<string> $mngJumpPlusRewardScheduleIds
     * @return Collection<DynJumpPlusRewardEntity>
     */
    public function getByMngJumpPlusRewardScheduleIds(
        string $bnUserId,
        Collection $mngJumpPlusRewardScheduleIds,
    ): Collection {
        if ($mngJumpPlusRewardScheduleIds->isEmpty()) {
            return collect();
        }

        $mngJumpPlusRewardScheduleIds = $mngJumpPlusRewardScheduleIds->unique();

        /**
         * 1回でリクエストできるアイテムの件数は100件まで。
         * 運用が進んだとしても一度に100件取得するケースはないと想定しているが、
         * 100件を超えとエラーになるので、万が一のために、101件以上なら切り捨てる。
         * 101件目以降は、次回のAPIリクエスト時に取得して配布する
         * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_BatchGetItem.html
         */
        $mngJumpPlusRewardScheduleIds = $mngJumpPlusRewardScheduleIds->take(self::BATCH_GET_ITEM_MAX_REQUEST_COUNT);

        $requestItems = [];
        foreach ($mngJumpPlusRewardScheduleIds as $mngJumpPlusRewardScheduleId) {
            $requestItems[] = [
                'bn_user_id' => ['S' => $bnUserId],
                'mst_reward_id' => ['S' => $mngJumpPlusRewardScheduleId],
            ];
        }

        $result = $this->client->batchGetItem([
            'RequestItems' => [
                $this->tableName => [
                    'Keys' => $requestItems,
                ],
            ],
        ]);

        if (is_null($result)) {
            return collect();
        }

        $entities = collect();
        $items = $result['Responses'][$this->tableName] ?? [];
        foreach ($items as $item) {
            $entities->push(new DynJumpPlusRewardEntity($item));
        }

        return $entities;
    }

    /**
     * 配布済みの報酬情報を受け取り済みに更新する
     *
     * 未受取状態のレコードのみ更新し、それ以外のステータスのレコードを更新しようとした場合はDynamoDBのupdateItemは失敗する
     *
     * @param \Illuminate\Support\Collection<DynJumpPlusRewardEntity> $dynJumpPlusRewards
     * @return \Illuminate\Support\Collection<DynJumpPlusRewardEntity> $dynJumpPlusRewards 受取済に更新できた報酬の配列
     */
    public function receiveRewards(Collection $dynJumpPlusRewards): Collection
    {
        $receiveds = collect();

        foreach ($dynJumpPlusRewards as $dynJumpPlusReward) {
            /** @var \App\Domain\Resource\Dyn\Entities\DynJumpPlusRewardEntity $dynJumpPlusReward */

            $result = $this->client->updateItem([
                'TableName' => $this->tableName,
                'Key' => [
                    'bn_user_id' => ['S' => $dynJumpPlusReward->getBnUserId()],
                    'mst_reward_id' => ['S' => $dynJumpPlusReward->getMngJumpPlusRewardScheduleId()],
                ],
                'UpdateExpression' => 'SET #status = :newStatus',
                'ConditionExpression' => '#status = :currentStatus',
                'ExpressionAttributeNames' => [
                    '#status' => 'status',
                ],
                'ExpressionAttributeValues' => [
                    ':newStatus' => ['N' => $this->getReceivedStatusString()],
                    ':currentStatus' => ['N' => $this->getNotReceivedStatusString()],
                ],
            ]);

            if (!$result) {
                continue;
            }
            $receiveds->push($dynJumpPlusReward);
        }

        return $receiveds;
    }
}
