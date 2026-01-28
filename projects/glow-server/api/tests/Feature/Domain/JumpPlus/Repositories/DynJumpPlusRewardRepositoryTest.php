<?php

namespace Tests\Feature\Domain\JumpPlus\Repositories;

use App\Domain\JumpPlus\Enums\DynJumpPlusRewardStatus;
use App\Domain\Resource\Dyn\Entities\DynJumpPlusRewardEntity;
use App\Infrastructure\DynamoDB\DynamoDbClient;
use Illuminate\Support\Collection;
use Tests\Feature\Domain\Common\Entities\TestDynamoDbClient;
use Tests\TestCase;

/**
 * DynamoDBを利用したDynJumpPlusRewardRepositoryのテスト
 *
 * 通常、テストではスキップする。
 * 新規追加時にDynamoDBアクセスができるかを確認する必要があったため追加したテストクラスです。
 */
class DynJumpPlusRewardRepositoryTest extends TestCase
{
    private TestDynJumpPlusRewardRepository $dynJumpPlusRewardRepository;

    // テストで必要なデータをdynamodbに作るために用意
    private DynamoDbClient $client;
    private string $tableName = '';

    protected function setUp(): void
    {
        parent::setUp();

        // 通常、テストではスキップする
        $this->skipIfSkipTestsEnabled();

        $this->client = new TestDynamoDbClient();
        $this->dynJumpPlusRewardRepository = new TestDynJumpPlusRewardRepository($this->client);
        $this->tableName = $this->dynJumpPlusRewardRepository->getTestTableName();
    }

    /**
     * 指定したbn_user_idの報酬情報をdynamodbから取得する
     */
    private function getRewards(string $bnUserId): Collection
    {
        $result = $this->client->query([
            'TableName' => $this->tableName,
            'KeyConditionExpression' => '#bn_user_id = :bn_user_id',
            'ExpressionAttributeNames' => [
                '#bn_user_id' => 'bn_user_id',
            ],
            'ExpressionAttributeValues' => [
                ':bn_user_id' => ['S' => $bnUserId],
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
     * dynamodbに報酬情報を作成する
     */
    private function createReward(string $bnUserId, string $mstJumpPlusRewardScheduleId, DynJumpPlusRewardStatus $status): DynJumpPlusRewardEntity
    {
        $isPut = $this->client->putItem([
            'TableName' => $this->tableName,
            'Item' => [
                'bn_user_id' => ['S' => $bnUserId],
                'mst_reward_id' => ['S' => $mstJumpPlusRewardScheduleId],
                'status' => ['N' => (string) $status->value],
            ],
        ]);

        if (!$isPut) {
            $this->fail('Failed to create reward');
        }

        return new DynJumpPlusRewardEntity([
            'bn_user_id' => ['S' => $bnUserId],
            'mst_reward_id' => ['S' => $mstJumpPlusRewardScheduleId],
            'status' => ['N' => (string) $status->value],
        ]);
    }

    /**
     * dynamodbの報酬情報を削除する
     */
    private function deleteReward(string $bnUserId, string $mstJumpPlusRewardScheduleId): bool
    {
        return $this->client->deleteItem([
            'TableName' => $this->tableName,
            'Key' => [
                'bn_user_id' => ['S' => $bnUserId],
                'mst_reward_id' => ['S' => $mstJumpPlusRewardScheduleId],
            ],
        ]);
    }

    public function test_getReceivableRewards_受取可の報酬情報を取得できる(): void
    {
        // Setup
        $bnUserId = 'test_bn_user_1';
        $otherUserId = 'test_bn_user_2';

        $this->createReward($bnUserId, 'reward1', DynJumpPlusRewardStatus::NOT_RECEIVED);
        $this->createReward($bnUserId, 'reward2', DynJumpPlusRewardStatus::NOT_RECEIVED);
        $this->createReward($bnUserId, 'reward3', DynJumpPlusRewardStatus::RECEIVED);
        $this->createReward($otherUserId, 'reward1', DynJumpPlusRewardStatus::NOT_RECEIVED);

        // Exercise
        $result = $this->dynJumpPlusRewardRepository->getReceivableRewards($bnUserId);

        // Verify
        $this->assertCount(2, $result);

        $actual = $result->first();
        $this->assertEquals($bnUserId, $actual->getBnUserId());
        $this->assertEquals('reward1', $actual->getMngJumpPlusRewardScheduleId());
        $this->assertEquals(DynJumpPlusRewardStatus::NOT_RECEIVED->value, $actual->getStatus());

        $actual = $result->last();
        $this->assertEquals($bnUserId, $actual->getBnUserId());
        $this->assertEquals('reward2', $actual->getMngJumpPlusRewardScheduleId());
        $this->assertEquals(DynJumpPlusRewardStatus::NOT_RECEIVED->value, $actual->getStatus());

        // Tear down
        $this->deleteReward($bnUserId, 'reward1');
        $this->deleteReward($bnUserId, 'reward2');
        $this->deleteReward($bnUserId, 'reward3');
        $this->deleteReward($otherUserId, 'reward1');
    }

    public function test_receiveRewards_報酬情報を受け取り済みに更新できる(): void
    {
        // Setup
        $bnUserId = 'test_bn_user_1';
        $otherUserId = 'test_bn_user_2';

        $rewards = collect();
        $rewards->push($this->createReward($bnUserId, 'reward1', DynJumpPlusRewardStatus::NOT_RECEIVED));
        $rewards->push($this->createReward($bnUserId, 'reward2', DynJumpPlusRewardStatus::NOT_RECEIVED));
        // これは更新されない
        $this->createReward($bnUserId, 'reward3', DynJumpPlusRewardStatus::NOT_RECEIVED);
        $this->createReward($otherUserId, 'reward1', DynJumpPlusRewardStatus::NOT_RECEIVED);

        // Exercise
        $this->dynJumpPlusRewardRepository->receiveRewards($rewards);

        // Verify
        // 対象ユーザー
        $entities = $this->getRewards($bnUserId)
            ->filter(fn (DynJumpPlusRewardEntity $reward) => $reward->getBnUserId() === $bnUserId)
            ->keyBy->getMngJumpPlusRewardScheduleId();
        $this->assertCount(3, $entities);
        $this->assertEquals(DynJumpPlusRewardStatus::RECEIVED->value, $entities['reward1']->getStatus());
        $this->assertEquals(DynJumpPlusRewardStatus::RECEIVED->value, $entities['reward2']->getStatus());
        $this->assertEquals(DynJumpPlusRewardStatus::NOT_RECEIVED->value, $entities['reward3']->getStatus());

        // 他ユーザー
        $entities = $this->getRewards($otherUserId)
            ->filter(fn (DynJumpPlusRewardEntity $reward) => $reward->getBnUserId() === $otherUserId)
            ->keyBy->getMngJumpPlusRewardScheduleId();
        $this->assertCount(1, $entities);
        $this->assertEquals(DynJumpPlusRewardStatus::NOT_RECEIVED->value, $entities['reward1']->getStatus());

        // Tear down
        $this->deleteReward($bnUserId, 'reward1');
        $this->deleteReward($bnUserId, 'reward2');
        $this->deleteReward($bnUserId, 'reward3');
        $this->deleteReward($otherUserId, 'reward1');
    }
}
