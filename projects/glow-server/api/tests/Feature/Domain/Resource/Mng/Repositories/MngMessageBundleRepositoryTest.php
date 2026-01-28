<?php

namespace Feature\Domain\Resource\Mng\Repositories;

use App\Domain\Common\Enums\Language;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Message\Enums\MngMessageType;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mng\Entities\MngMessageBundle;
use App\Domain\Resource\Mng\Models\MngMessage;
use App\Domain\Resource\Mng\Models\MngMessageI18n;
use App\Domain\Resource\Mng\Models\MngMessageReward;
use App\Domain\Resource\Mng\Repositories\MngMessageBundleRepository;
use Tests\TestCase;

class MngMessageBundleRepositoryTest extends TestCase
{
    private MngMessageBundleRepository $mngMessageBundleRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->mngMessageBundleRepository = app()->make(MngMessageBundleRepository::class);
    }


    /**
     * getActiveMngMessageBundlesByLanguageの基本実行テスト
     */
    public function test_getActiveMngMessageBundlesByLanguage_期間内メッセージ取得(): void
    {
        // Setup
        $now = $this->fixTime('2023-01-15 12:00:00');

        // メッセージデータを作成
        MngMessage::factory()->createMany([
            // 期限内
            [
                'id' => 'msg_001',
                'start_at' => '2023-01-10 00:00:00',
                'expired_at' => '2023-01-20 23:59:59',
                'type' => MngMessageType::ALL->value,
                'add_expired_days' => 0,
            ],
            // 期限外
            [
                // 未来
                'id' => 'msg_002',
                'start_at' => '2023-01-16 00:00:00',
                'expired_at' => '2023-01-25 23:59:59',
                'type' => MngMessageType::ALL->value,
                'add_expired_days' => 0,
            ],
            [
                // 過去
                'id' => 'msg_003',
                'start_at' => '2023-01-01 00:00:00',
                'expired_at' => '2023-01-13 23:59:59',
                'type' => MngMessageType::ALL->value,
                'add_expired_days' => 0,
            ]
        ]);

        // I18nデータを作成（全メッセージ分）
        MngMessageI18n::factory()->createMany([
            [
                'mng_message_id' => 'msg_001',
                'language' => Language::Ja->value,
                'title' => 'テストメッセージ_msg_001',
                'body' => 'これはテスト用のメッセージです_msg_001',
            ],
            [
                'mng_message_id' => 'msg_002',
                'language' => Language::Ja->value,
                'title' => 'テストメッセージ_msg_002',
                'body' => 'これはテスト用のメッセージです_msg_002',
            ],
            [
                'mng_message_id' => 'msg_003',
                'language' => Language::Ja->value,
                'title' => 'テストメッセージ_msg_003',
                'body' => 'これはテスト用のメッセージです_msg_003',
            ]
        ]);

        // 報酬データを作成（アクティブなメッセージのみ）
        MngMessageReward::factory()->create([
            'mng_message_id' => 'msg_001',
            'resource_type' => RewardType::COIN->value,
            'resource_amount' => 1000,
            'display_order' => 1,
        ]);

        // Exercise
        $result = $this->mngMessageBundleRepository->getActiveMngMessageBundlesByLanguage(Language::Ja->value, $now);

        // Verify
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
        $this->assertCount(1, $result);
        $this->assertTrue($result->has('msg_001'));

        /** @var MngMessageBundle $bundle */
        $bundle = $result->get('msg_001');
        $this->assertInstanceOf(MngMessageBundle::class, $bundle);

        // MngMessageの確認
        $this->assertEquals('msg_001', $bundle->getMngMessage()->getId());
        $this->assertEquals(MngMessageType::ALL->value, $bundle->getMngMessage()->getType());

        // MngMessageI18nの確認
        $this->assertEquals('テストメッセージ_msg_001', $bundle->getMngMessageI18n()->getTitle());
        $this->assertEquals('これはテスト用のメッセージです_msg_001', $bundle->getMngMessageI18n()->getBody());
        $this->assertEquals(Language::Ja->value, $bundle->getMngMessageI18n()->getLanguage());

        // MngMessageRewardsの確認
        $this->assertCount(1, $bundle->getMngMessageRewards());
        $reward = $bundle->getMngMessageRewards()->first();
        $this->assertEquals(RewardType::COIN->value, $reward->getResourceType());
        $this->assertEquals(1000, $reward->getResourceAmount());
        $this->assertEquals(1, $reward->getDisplayOrder());
    }

    /**
     * getMngMessageBundlesByLanguageAndMngMessageIdsの基本実行テスト
     */
    public function test_getMngMessageBundlesByLanguageAndMngMessageIds_指定ID絞り込み(): void
    {
        // Setup
        $now = $this->fixTime('2023-01-15 12:00:00');

        // 複数のアクティブなメッセージを作成
        MngMessage::factory()->createMany([
            [
                'id' => 'msg_001',
                'start_at' => '2023-01-10 00:00:00',
                'expired_at' => '2023-01-20 23:59:59',
                'type' => MngMessageType::ALL->value,
            ],
            [
                'id' => 'msg_002',
                'start_at' => '2023-01-10 00:00:00',
                'expired_at' => '2023-01-20 23:59:59',
                'type' => MngMessageType::ALL->value,
            ],
            // 配布期間＋受け取り期間の期限内
            [
                'id' => 'msg_003',
                'start_at' => '2023-01-10 00:00:00',
                'expired_at' => '2023-01-14 23:59:59',
                'type' => MngMessageType::ALL->value,
                'add_expired_days' => 1,
            ],
            // 配布期間＋受け取り期間の期限切れ
            [
                'id' => 'msg_004',
                'start_at' => '2023-01-10 00:00:00',
                'expired_at' => '2023-01-14 23:59:59',
                'type' => MngMessageType::ALL->value,
                'add_expired_days' => 0,
            ]
        ]);

        MngMessageI18n::factory()->createMany([
            [
                'mng_message_id' => 'msg_001',
                'language' => Language::Ja->value,
                'title' => 'タイトル_msg_001',
                'body' => '本文_msg_001',
            ],
            [
                'mng_message_id' => 'msg_002',
                'language' => Language::Ja->value,
                'title' => 'タイトル_msg_002',
                'body' => '本文_msg_002',
            ],
            [
                'mng_message_id' => 'msg_003',
                'language' => Language::Ja->value,
                'title' => 'タイトル_msg_003',
                'body' => '本文_msg_003',
            ]
        ]);

        MngMessageReward::factory()->createMany([
            [
                'mng_message_id' => 'msg_001',
                'resource_type' => RewardType::COIN->value,
                'resource_amount' => 1000,
                'display_order' => 1,
            ],
            [
                'mng_message_id' => 'msg_002',
                'resource_type' => RewardType::COIN->value,
                'resource_amount' => 1000,
                'display_order' => 1,
            ],
            [
                'mng_message_id' => 'msg_003',
                'resource_type' => RewardType::COIN->value,
                'resource_amount' => 1000,
                'display_order' => 1,
            ],
            [
                'mng_message_id' => 'msg_004',
                'resource_type' => RewardType::COIN->value,
                'resource_amount' => 1000,
                'display_order' => 1,
            ]
        ]);

        // Exercise
        $targetIds = collect(['msg_001', 'msg_003', 'msg_004']);
        $result = $this->mngMessageBundleRepository->getMngMessageBundlesByLanguageAndMngMessageIds(
            Language::Ja->value,
            $targetIds,
            $now
        );

        // Verify
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
        $this->assertCount(2, $result);
        $this->assertTrue($result->has('msg_001'));
        $this->assertFalse($result->has('msg_002')); // 指定していないのでなし
        $this->assertTrue($result->has('msg_003'));
        $this->assertFalse($result->has('msg_004')); // 配布期間＋受け取り期間の期限切れなのでなし

        // 各バンドルの内容確認
        /** @var MngMessageBundle $bundle1 */
        $bundle1 = $result->get('msg_001');
        $this->assertEquals('msg_001', $bundle1->getMngMessage()->getId());
        $this->assertEquals('タイトル_msg_001', $bundle1->getMngMessageI18n()->getTitle());

        /** @var MngMessageBundle $bundle3 */
        $bundle3 = $result->get('msg_003');
        $this->assertEquals('msg_003', $bundle3->getMngMessage()->getId());
        $this->assertEquals('タイトル_msg_003', $bundle3->getMngMessageI18n()->getTitle());
    }

    /**
     * createAndCacheMngMessageBundlesの基本実行テスト（間接的にテスト）
     * add_expired_daysを考慮したgetFinalExpiredAt()によるフィルタリングをテスト
     */
    public function test_getMngMessageBundles_バンドル作成とキャッシュ(): void
    {
        // Setup
        $now = $this->fixTime('2023-01-15 12:00:00');

        // 複数のメッセージとその関連データを作成
        MngMessage::factory()->createMany([
            // 期限内
            [
                'id' => 'msg_001',
                'start_at' => '2023-01-10 00:00:00',
                'expired_at' => '2023-01-20 23:59:59',
                'type' => MngMessageType::ALL->value,
                'add_expired_days' => 7, // 最終期限は2023-01-27 23:59:59
            ],
            // 基本の期限は過ぎているが、add_expired_daysを考慮すると含まれるもの
            [
                'id' => 'msg_002',
                'start_at' => '2023-01-01 00:00:00',
                'expired_at' => '2023-01-10 23:59:59',
                'type' => MngMessageType::INDIVIDUAL->value,
                'add_expired_days' => 14, // 最終期限は2023-01-24 23:59:59
            ],
            // add_expired_daysを考慮しても期限切れのもの
            [
                'id' => 'msg_003',
                'start_at' => '2023-01-01 00:00:00',
                'expired_at' => '2023-01-10 23:59:59',
                'type' => MngMessageType::ALL->value,
                'add_expired_days' => 3, // 最終期限は2023-01-13 23:59:59（過去）
            ]
        ]);

        // I18nデータ（期限切れでないメッセージ分＋期限切れのメッセージ分）
        MngMessageI18n::factory()->createMany([
            [
                'mng_message_id' => 'msg_001',
                'language' => Language::Ja->value,
                'title' => 'キャッシュテストメッセージ1',
                'body' => 'キャッシュテスト用の本文1',
            ],
            [
                'mng_message_id' => 'msg_002',
                'language' => Language::Ja->value,
                'title' => 'キャッシュテストメッセージ2',
                'body' => 'キャッシュテスト用の本文2',
            ],
            [
                'mng_message_id' => 'msg_003',
                'language' => Language::Ja->value,
                'title' => 'キャッシュテストメッセージ3（期限切れ）',
                'body' => 'キャッシュテスト用の本文3（期限切れ）',
            ]
        ]);

        // 報酬データ（期限切れでないメッセージ分＋期限切れのメッセージ分）
        MngMessageReward::factory()->createMany([
            [
                'mng_message_id' => 'msg_001',
                'resource_type' => RewardType::COIN->value,
                'resource_amount' => 1000,
                'display_order' => 2,
            ],
            [
                'mng_message_id' => 'msg_001',
                'resource_type' => RewardType::EXP->value,
                'resource_amount' => 500,
                'display_order' => 1,
            ],
            [
                'mng_message_id' => 'msg_002',
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_amount' => 100,
                'display_order' => 1,
            ],
            [
                'mng_message_id' => 'msg_003',
                'resource_type' => RewardType::COIN->value,
                'resource_amount' => 500,
                'display_order' => 1,
            ]
        ]);

        // Exercise
        $result = $this->execPrivateMethod(
            $this->mngMessageBundleRepository,
            'getMngMessageBundles',
            [Language::Ja->value, $now]
        );

        // Verify
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
        // add_expired_daysを考慮した結果、msg_001とmsg_002のみが含まれること
        $this->assertCount(2, $result);
        $this->assertTrue($result->has('msg_001'));
        $this->assertTrue($result->has('msg_002'));
        $this->assertFalse($result->has('msg_003')); // add_expired_daysを考慮しても期限切れ

        // msg_001の確認
        /** @var MngMessageBundle $bundle1 */
        $bundle1 = $result->get('msg_001');
        $this->assertEquals('msg_001', $bundle1->getMngMessage()->getId());
        $this->assertEquals(MngMessageType::ALL->value, $bundle1->getMngMessage()->getType());
        $this->assertEquals(7, $bundle1->getMngMessage()->getAddExpiredDays());
        $this->assertEquals('キャッシュテストメッセージ1', $bundle1->getMngMessageI18n()->getTitle());

        // 報酬の順序確認（複数報酬）
        $rewards1 = $bundle1->getMngMessageRewards();
        $this->assertCount(2, $rewards1);
        $firstReward = $rewards1->first();
        $lastReward = $rewards1->last();
        $this->assertEquals(RewardType::EXP->value, $firstReward->getResourceType());
        $this->assertEquals(RewardType::COIN->value, $lastReward->getResourceType());

        // msg_002の確認（基本の期限は過ぎているが、add_expired_daysを考慮して含まれる）
        /** @var MngMessageBundle $bundle2 */
        $bundle2 = $result->get('msg_002');
        $this->assertEquals('msg_002', $bundle2->getMngMessage()->getId());
        $this->assertEquals(MngMessageType::INDIVIDUAL->value, $bundle2->getMngMessage()->getType());
        $this->assertEquals(14, $bundle2->getMngMessage()->getAddExpiredDays());
        $this->assertEquals('キャッシュテストメッセージ2', $bundle2->getMngMessageI18n()->getTitle());

        $rewards2 = $bundle2->getMngMessageRewards();
        $this->assertCount(1, $rewards2);
        $this->assertEquals(RewardType::FREE_DIAMOND->value, $rewards2->first()->getResourceType());

        // キャッシュされていることを確認
        $cacheKey = CacheKeyUtil::getMngMessageBundleKey(Language::Ja->value);
        $cachedData = $this->getFromRedis($cacheKey);
        $this->assertNotNull($cachedData, 'キャッシュにデータが保存されていること');
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $cachedData);
        // キャッシュには期限切れでないメッセージ（msg_001, msg_002）のみが保存されること
        $this->assertCount(2, $cachedData);
        $this->assertTrue($cachedData->has('msg_001'));
        $this->assertTrue($cachedData->has('msg_002'));
        $this->assertFalse($cachedData->has('msg_003')); // 期限切れのメッセージはキャッシュされない
    }
}
