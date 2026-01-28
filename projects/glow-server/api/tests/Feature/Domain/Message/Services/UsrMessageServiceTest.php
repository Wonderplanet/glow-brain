<?php

namespace Feature\Domain\Message\Services;

use App\Domain\AdventBattle\Enums\AdventBattleRewardCategory;
use App\Domain\AdventBattle\Models\UsrAdventBattle;
use App\Domain\Common\Enums\Language;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\JumpPlus\Delegators\JumpPlusDelegator;
use App\Domain\JumpPlus\Enums\DynJumpPlusRewardStatus;
use App\Domain\Message\Constants\MessageConstant;
use App\Domain\Message\Enums\MessageSource;
use App\Domain\Message\Enums\MngMessageType;
use App\Domain\Message\Models\Eloquent\UsrMessage;
use App\Domain\Message\Models\UsrTemporaryIndividualMessage;
use App\Domain\Message\Repositories\UsrMessageRepository;
use App\Domain\Message\Services\UsrMessageService;
use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Pvp\Enums\PvpRewardCategory;
use App\Domain\Pvp\Models\SysPvpSeason;
use App\Domain\Pvp\Models\UsrPvp;
use App\Domain\Pvp\Repositories\UsrPvpRepository;
use App\Domain\Pvp\Services\PvpCacheService;
use App\Domain\Pvp\Services\PvpService;
use App\Domain\Resource\Entities\JumpPlusRewardBundle;
use App\Domain\Resource\Entities\Rewards\JumpPlusReward;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mng\Models\MngMessage;
use App\Domain\Resource\Mng\Models\MngMessageI18n;
use App\Domain\Resource\Mst\Models\MstAdventBattle;
use App\Domain\Resource\Mst\Models\MstAdventBattleReward;
use App\Domain\Resource\Mst\Models\MstAdventBattleRewardGroup;
use App\Domain\Resource\Mst\Models\MstPvpRank;
use App\Domain\Resource\Mst\Models\MstPvpReward;
use App\Domain\Resource\Mst\Models\MstPvpRewardGroup;
use App\Domain\User\Models\UsrUserLogin;
use App\Domain\User\Models\UsrUserParameter;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Redis;
use Tests\Feature\Domain\JumpPlus\Entities\TestDynJumpPlusRewardEntity;
use Tests\Feature\Domain\Reward\Test1Reward;
use Tests\TestCase;

class UsrMessageServiceTest extends TestCase
{
    private UsrMessageService $usrMessageService;
    private UsrMessageRepository $usrMessageRepository;
    private PvpService $pvpService;

    public function setUp(): void
    {
        parent::setUp();

        $this->usrMessageService = app()->make(UsrMessageService::class);
        $this->usrMessageRepository = app()->make(UsrMessageRepository::class);
        $this->pvpService = app()->make(PvpService::class);
    }

    public function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function addNewMessages_登録チェック(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        CarbonImmutable::setTestNow('2020-01-15 00:00:00');
        $now = CarbonImmutable::now();

        // gameStartAtを設定
        $usrUser->game_start_at = $now->subDays(2)->toDateTimeString();
        $usrUser->save();

        UsrUserLogin::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'first_login_at' => $now->subDays(2)->toDateTimeString(),
        ]);

        //  表示期限がすぎている
        MngMessage::factory()
            ->set('id', 'out1')
            ->set('start_at', '2019-12-01 00:00:00')
            ->set('expired_at', '2019-12-31 23:59:59')
            ->create();
        MngMessageI18n::factory()->create([
            'mng_message_id' => 'out1',
            'language' => Language::Ja->value,
            'title' => 'タイトル',
            'body' => '本文',
        ]);
        // 登録済み(全体配布、既読済み)
        MngMessage::factory()
            ->set('id', 'message_1')
            ->set('start_at', '2020-01-01 00:01:00')
            ->set('add_expired_days', 7)
            ->create();
        MngMessageI18n::factory()->create([
            'mng_message_id' => 'message_1',
            'language' => Language::Ja->value,
            'title' => 'タイトル1',
            'body' => '本文1',
        ]);
        $usrMessage1 = UsrMessage::factory()
            ->set('usr_user_id', $usrUser->getId())
            ->set('mng_message_id', 'message_1')
            ->set('message_source', MessageSource::MNG_MESSAGE->value)
            ->set('received_at', null)
            ->set('opened_at', '2020-01-08 11:10:00')
            ->set('expired_at', '2020-01-15 11:10:00') // add_expired_daysが加算された状態として登録
            ->create();
        // 全体配布、未登録
        $mngMessage2 = MngMessage::factory()
            ->set('id', 'message_2')
            ->set('start_at', '2020-01-02 00:00:00')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->set('add_expired_days', 7)
            ->create();
        MngMessageI18n::factory()->create([
            'mng_message_id' => $mngMessage2->id,
            'language' => Language::Ja->value,
            'title' => 'タイトル2',
            'body' => '本文2',
        ]);
        // 登録済み(個別配布、既読済み)
        MngMessage::factory()
            ->set('id', 'message_3')
            ->set('start_at', '2020-01-01 00:01:00')
            ->set('type', MngMessageType::INDIVIDUAL->value)
            ->set('add_expired_days', 7)
            ->create();
        MngMessageI18n::factory()->create([
            'mng_message_id' => 'message_3',
            'language' => Language::Ja->value,
            'title' => 'タイトル3',
            'body' => '本文3',
        ]);
        UsrTemporaryIndividualMessage::factory()
            ->set('usr_user_id', $usrUser->getId())
            ->set('mng_message_id', 'message_3')
            ->create();
        $usrMessage3 = UsrMessage::factory()
            ->set('usr_user_id', $usrUser->getId())
            ->set('mng_message_id', 'message_3')
            ->set('message_source', MessageSource::MNG_MESSAGE->value)
            ->set('received_at', null)
            ->set('opened_at', '2020-01-08 11:10:00')
            ->set('expired_at', '2020-01-15 11:10:00') // add_expired_daysが加算された状態として登録
            ->create();
        // 個別配布、未登録
        $mngMessage4 = MngMessage::factory()
            ->set('id', 'message_4')
            ->set('start_at', '2020-01-02 00:00:00')
            ->set('type', MngMessageType::INDIVIDUAL->value)
            ->set('expired_at', '2020-01-31 23:59:59')
            ->set('add_expired_days', 10)
            ->create();
        MngMessageI18n::factory()->create([
            'mng_message_id' => $mngMessage4->id,
            'language' => Language::Ja->value,
            'title' => 'タイトル4',
            'body' => '本文4',
        ]);
        UsrTemporaryIndividualMessage::factory()
            ->set('usr_user_id', $usrUser->getId())
            ->set('mng_message_id', 'message_4')
            ->create();

        // Exercise
        $gameStartAt = CarbonImmutable::parse($usrUser->getGameStartAt());
        $this->usrMessageService->addNewMessages($usrUser->getId(), $now, Language::Ja->value, $gameStartAt);
        $this->saveAll();

        // Verify
        $usrMessageResult = $this->usrMessageRepository->getReceivableList($usrUser->getId(), CarbonImmutable::now())
            ->keyBy(fn($row) => $row->getMngMessageId());

        //  メッセージが4件登録されている
        $this->assertCount(4, $usrMessageResult);
        //  登録済みメッセージのチェック
        //  全体配布
        // $resultUsrMessage1 = $usrMessageResult->firstWhere(fn (UsrMessage $row) => $row->getMngMessageId() === $usrMessage1->getMngMessageId());
        $resultUsrMessage1 = $usrMessageResult->get($usrMessage1->getMngMessageId());
        $this->assertEquals($usrMessage1->getMngMessageId(), $resultUsrMessage1->getMngMessageId());
        $this->assertEquals($usrMessage1->getMessageSource(), $resultUsrMessage1->getMessageSource());
        $this->assertEquals($usrMessage1->getOpenedAt(), $resultUsrMessage1->getOpenedAt());
        $this->assertEquals($usrMessage1->getReceivedAt(), $resultUsrMessage1->getReceivedAt());
        $this->assertEquals($usrMessage1->getExpiredAt(), $resultUsrMessage1->getExpiredAt());
        //  個別配布
        // $resultUsrMessage2 = $usrMessageResult->firstWhere(fn (UsrMessage $row) => $row->getMngMessageId() === $usrMessage3->getMngMessageId());
        $resultUsrMessage2 = $usrMessageResult->get($usrMessage3->getMngMessageId());
        $this->assertEquals($usrMessage3->getMngMessageId(), $resultUsrMessage2->getMngMessageId());
        $this->assertEquals($usrMessage3->getMessageSource(), $resultUsrMessage2->getMessageSource());
        $this->assertEquals($usrMessage3->getOpenedAt(), $resultUsrMessage2->getOpenedAt());
        $this->assertEquals($usrMessage3->getReceivedAt(), $resultUsrMessage2->getReceivedAt());
        $this->assertEquals($usrMessage3->getExpiredAt(), $resultUsrMessage2->getExpiredAt());

        //  新規メッセージのチェック
        //  全体配布
        // $resultUsrMessage3 = $usrMessageResult->firstWhere(fn (UsrMessage $row) => $row->getMngMessageId() === $mngMessage2->id);
        $resultUsrMessage3 = $usrMessageResult->get($mngMessage2->id);
        $this->assertEquals($mngMessage2->id, $resultUsrMessage3->getMngMessageId());
        $this->assertEquals(MessageSource::MNG_MESSAGE->value, $resultUsrMessage3->getMessageSource());
        $this->assertEquals($mngMessage2->opened_at, $resultUsrMessage3->getOpenedAt());
        $this->assertEquals($mngMessage2->received_at, $resultUsrMessage3->getReceivedAt());
        $this->assertEquals($now->addDays(7), $resultUsrMessage3->getExpiredAt());
        //  個別配布
        // $resultUsrMessage4 = $usrMessageResult->firstWhere(fn (UsrMessage $row) => $row->getMngMessageId() === $mngMessage4->id);
        $resultUsrMessage4 = $usrMessageResult->get($mngMessage4->id);
        $this->assertEquals($mngMessage4->id, $resultUsrMessage4->getMngMessageId());
        $this->assertEquals(MessageSource::MNG_MESSAGE->value, $resultUsrMessage4->getMessageSource());
        $this->assertEquals($mngMessage4->opened_at, $resultUsrMessage4->getOpenedAt());
        $this->assertEquals($mngMessage4->received_at, $resultUsrMessage4->getReceivedAt());
        $this->assertEquals($now->addDays(10), $resultUsrMessage4->getExpiredAt());
    }

    /**
     * @test
     */
    public function addNewMessages_全て登録済みのチェック(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        CarbonImmutable::setTestNow('2020-01-15 00:00:00');
        $now = CarbonImmutable::now();

        // gameStartAtを設定
        $usrUser->game_start_at = $now->subDays(2)->toDateTimeString();
        $usrUser->save();

        UsrUserLogin::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'first_login_at' => $now->subDays(2)->toDateTimeString(),
        ]);

        // 登録済み(全体配布、既読済み)
        MngMessage::factory()
            ->set('id', 'message_1')
            ->set('start_at', '2020-01-01 00:01:00')
            ->create();
        MngMessageI18n::factory()->create([
            'mng_message_id' => 'message_1',
            'language' => Language::Ja->value,
            'title' => 'タイトル1',
            'body' => '本文1',
        ]);
        $usrMessage1 = UsrMessage::factory()
            ->set('usr_user_id', $usrUser->getId())
            ->set('mng_message_id', 'message_1')
            ->set('message_source', MessageSource::MNG_MESSAGE->value)
            ->set('received_at', null)
            ->set('opened_at', '2020-01-03 11:10:00')
            ->create();
        // 登録済み(個別配布、未既読)
        MngMessage::factory()
            ->set('id', 'message_2')
            ->set('start_at', '2020-01-01 00:01:00')
            ->set('type', MngMessageType::INDIVIDUAL->value)
            ->set('add_expired_days', 7)
            ->create();
        MngMessageI18n::factory()->create([
            'mng_message_id' => 'message_2',
            'language' => Language::Ja->value,
            'title' => 'タイトル2',
            'body' => '本文2',
        ]);
        UsrTemporaryIndividualMessage::factory()
            ->set('usr_user_id', $usrUser->getId())
            ->set('mng_message_id', 'message_2')
            ->create();

        // Exercise
        $gameStartAt = CarbonImmutable::parse($usrUser->getGameStartAt());
        $this->usrMessageService->addNewMessages($usrUser->getId(), $now, Language::Ja->value, $gameStartAt);
        $this->saveAll();

        // Verify
        $usrMessageResult = $this->usrMessageRepository->getReceivableList($usrUser->getId(), CarbonImmutable::now())
            ->keyBy(fn($row) => $row->getMngMessageId());

        //  メッセージが2件
        $this->assertCount(2, $usrMessageResult);
        //  登録済みメッセージの中身に変更がないことをチェック
        $resultUsrMessage1 = $usrMessageResult->get($usrMessage1->getMngMessageId());
        $this->assertEquals($usrMessage1->getMngMessageId(), $resultUsrMessage1->getMngMessageId());
        $this->assertEquals($usrMessage1->getMessageSource(), $resultUsrMessage1->getMessageSource());
        $this->assertEquals($usrMessage1->getOpenedAt(), $resultUsrMessage1->getOpenedAt());
        $this->assertEquals($usrMessage1->getReceivedAt(), $resultUsrMessage1->getReceivedAt());
        $this->assertEquals($usrMessage1->getExpiredAt(), $resultUsrMessage1->getExpiredAt());
        $resultUsrMessage2 = $usrMessageResult->get('message_2');
        $this->assertEquals('message_2', $resultUsrMessage2->getMngMessageId());
        $this->assertEquals(MessageSource::MNG_MESSAGE->value, $resultUsrMessage2->getMessageSource());
        $this->assertEquals(null, $resultUsrMessage2->getOpenedAt());
        $this->assertEquals(null, $resultUsrMessage2->getReceivedAt());
        // addExpiredDaysが加算された状態として登録
        $this->assertEquals('2020-01-22 00:00:00', $resultUsrMessage2->getExpiredAt());
    }

    /**
     * @test
     */
    public function addNewMessages_個別配布登録で対象ユーザーでなかったチェック(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        CarbonImmutable::setTestNow('2020-01-15 00:00:00');
        $now = CarbonImmutable::now();

        // gameStartAtを設定
        $usrUser->game_start_at = $now->subDays(2)->toDateTimeString();
        $usrUser->save();

        UsrUserLogin::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'first_login_at' => $now->subDays(2)->toDateTimeString(),
        ]);
        // 個別配布、未登録
        MngMessage::factory()
            ->set('id', 'message')
            ->set('start_at', '2020-01-02 00:00:00')
            ->set('type', MngMessageType::INDIVIDUAL->value)
            ->set('expired_at', '2020-01-31 23:59:59')
            ->set('add_expired_days', 10)
            ->create();
        UsrTemporaryIndividualMessage::factory()
            ->set('usr_user_id', 'test-000')
            ->set('mng_message_id', 'message')
            ->create();

        // Exercise
        $gameStartAt = CarbonImmutable::parse($usrUser->getGameStartAt());
        $this->usrMessageService->addNewMessages($usrUser->getId(), $now, Language::Ja->value, $gameStartAt);
        $this->saveAll();

        // Verify
        $usrMessageResult = $this->usrMessageRepository->getReceivableList($usrUser->getId(), CarbonImmutable::now());

        //  メッセージが0件
        $this->assertCount(0, $usrMessageResult);
    }

    public function testAddNewMessage_降臨バトル報酬がメッセージ登録されること(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $now = $this->fixTime();
        UsrUserLogin::factory()->create([
            'usr_user_id' => $usrUserId,
            'first_login_at' => $now->toDateTimeString(),
        ]);

        $mstAdventBattleId = MstAdventBattle::factory()->create([
            'start_at' => $now->subWeek(),
            'end_at' => $now->subDays(3),
        ])->toEntity()->getId();

        MstAdventBattleRewardGroup::factory()->create([
            'id' => 'ranking1',
            'mst_advent_battle_id' => $mstAdventBattleId,
            'reward_category' => AdventBattleRewardCategory::RANKING->value,
            'condition_value' => '1'
        ]);

        MstAdventBattleReward::factory()->create([
            'mst_advent_battle_reward_group_id' => 'ranking1',
            'resource_type' => RewardType::COIN->value,
            'resource_id' => null,
            'resource_amount' => 10000,
        ]);

        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'is_ranking_reward_received' => false,
            'max_score' => 1,
        ]);

        $key = CacheKeyUtil::getAdventBattleRankingKey($mstAdventBattleId);
        Redis::connection()->zadd($key, [$usrUserId => 1]);

        // Exercise
        $gameStartAt = CarbonImmutable::parse($usrUser->getGameStartAt());
        $this->usrMessageService->addNewMessages($usrUserId, $now, Language::Ja->value, $gameStartAt);
        $this->saveAll();

        // Verify
        $usrMessage = UsrMessage::query()->where('usr_user_id', $usrUserId)->get()->first();
        $this->assertNotNull($usrMessage);

        $this->assertNull($usrMessage->getMngMessageId());
        $this->assertEquals(RewardType::COIN->value, $usrMessage->getResourceType());
        $this->assertNull($usrMessage->getResourceId());
        $this->assertEquals(10000, $usrMessage->getResourceAmount());
        $this->assertEquals(MessageConstant::ADVENT_BATTLE_TITLE, $usrMessage->getTitle());
        $this->assertEquals(MessageConstant::ADVENT_BATTLE_BODY, $usrMessage->getBody());
        $expectedExpireAt = $now
            ->addDays(MessageConstant::ADVENT_BATTLE_REWARD_MESSAGE_EXPIRATION_DAYS)
            ->toDateTimeString();
        $this->assertEquals($expectedExpireAt, $usrMessage->getExpiredAt());
    }

    public function testAddNewMessage_降臨バトル未受取ハイスコア報酬がメッセージ登録されること(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $now = $this->fixTime();
        UsrUserLogin::factory()->create([
            'usr_user_id' => $usrUserId,
            'first_login_at' => $now->toDateTimeString(),
        ]);

        $mstAdventBattleId = MstAdventBattle::factory()->create([
            'start_at' => $now->subWeek(),
            'end_at' => $now->subDays(3),
        ])->toEntity()->getId();

        MstAdventBattleRewardGroup::factory()->create([
            'id' => 'ranking1',
            'mst_advent_battle_id' => $mstAdventBattleId,
            'reward_category' => AdventBattleRewardCategory::MAX_SCORE->value,
            'condition_value' => '1'
        ]);

        MstAdventBattleReward::factory()->create([
            'mst_advent_battle_reward_group_id' => 'ranking1',
            'resource_type' => RewardType::COIN->value,
            'resource_id' => null,
            'resource_amount' => 10000,
        ]);

        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'is_ranking_reward_received' => false,
            'max_score' => 10,
        ]);

        $key = CacheKeyUtil::getAdventBattleRankingKey($mstAdventBattleId);
        Redis::connection()->zadd($key, [$usrUserId => 1]);

        // Exercise
        $gameStartAt = CarbonImmutable::parse($usrUser->getGameStartAt());
        $this->usrMessageService->addNewMessages($usrUserId, $now, Language::Ja->value, $gameStartAt);
        $this->saveAll();

        // Verify
        $usrMessage = UsrMessage::query()->where('usr_user_id', $usrUserId)->get()->first();
        $this->assertNotNull($usrMessage);

        $this->assertNull($usrMessage->getMngMessageId());
        $this->assertEquals(RewardType::COIN->value, $usrMessage->getResourceType());
        $this->assertNull($usrMessage->getResourceId());
        $this->assertEquals(10000, $usrMessage->getResourceAmount());
        $this->assertEquals(MessageConstant::ADVENT_BATTLE_MAX_SCORE_TITLE, $usrMessage->getTitle());
        $this->assertEquals(MessageConstant::ADVENT_BATTLE_MAX_SCORE_BODY, $usrMessage->getBody());
        $expectedExpireAt = $now
            ->addDays(MessageConstant::ADVENT_BATTLE_REWARD_MESSAGE_EXPIRATION_DAYS)
            ->toDateTimeString();
        $this->assertEquals($expectedExpireAt, $usrMessage->getExpiredAt());
    }

    public function test_addNewMessages_ジャンプラ連携報酬がメッセージ登録される(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $now = $this->fixTime();
        UsrUserLogin::factory()->create(['usr_user_id' => $usrUserId]);

        // JumpPlusDelegatorをmockするために、UsrMessageServiceのインスタンスを再生成
        $this->app->when(UsrMessageService::class)
            ->needs(JumpPlusDelegator::class)
            ->give(function () use ($now) {
                $mock = $this->mock(JumpPlusDelegator::class);
                $mock->shouldReceive('getReceivableRewards')->andReturn(
                    collect([
                        new JumpPlusRewardBundle(
                            new TestDynJumpPlusRewardEntity('bn_user_id', 'reward1', DynJumpPlusRewardStatus::NOT_RECEIVED),
                            collect([new JumpPlusReward(RewardType::COIN->value, null, 999, 'reward1', $now->addDays(7)->toDateTimeString()),])
                        ),
                        new JumpPlusRewardBundle(
                            new TestDynJumpPlusRewardEntity('bn_user_id', 'reward2', DynJumpPlusRewardStatus::NOT_RECEIVED),
                            collect([new JumpPlusReward(RewardType::ITEM->value, 'item2', 111, 'reward2', $now->addDays(30)->toDateTimeString()),])
                        ),
                    ])
                );
                $mock->shouldReceive('markRewardsAsReceived')->andReturn();
                return $mock;
            });
        $usrMessageService = $this->app->make(UsrMessageService::class);

        // Exercise
        $gameStartAt = CarbonImmutable::parse($usrUser->getGameStartAt());
        $usrMessageService->addNewMessages($usrUserId, $now, Language::Ja->value, $gameStartAt);
        $usrMessageService->addUsrMessagesForJumpPlusReward($usrUserId, $now);
        $this->saveAll();

        // Verify
        $usrMessages = UsrMessage::query()->where('usr_user_id', $usrUserId)->get()->keyBy('resource_type');
        $this->assertCount(2, $usrMessages);

        // reward1
        $actual = $usrMessages->get(RewardType::COIN->value);
        $this->assertNotNull($actual);
        $this->assertEquals(RewardType::COIN->value, $actual->getResourceType());
        $this->assertNull($actual->getResourceId());
        $this->assertEquals(999, $actual->getResourceAmount());
        $this->assertEquals(MessageConstant::JUMP_PLUS_TITLE, $actual->getTitle());
        $this->assertEquals(MessageConstant::JUMP_PLUS_BODY, $actual->getBody());
        $this->assertEquals($now->addDays(7)->toDateTimeString(), $actual->getExpiredAt());
        $this->assertNull($actual->getReceivedAt());

        // reward2
        $actual = $usrMessages->get(RewardType::ITEM->value);
        $this->assertNotNull($actual);
        $this->assertEquals(RewardType::ITEM->value, $actual->getResourceType());
        $this->assertEquals('item2', $actual->getResourceId());
        $this->assertEquals(111, $actual->getResourceAmount());
        $this->assertEquals(MessageConstant::JUMP_PLUS_TITLE, $actual->getTitle());
        $this->assertEquals(MessageConstant::JUMP_PLUS_BODY, $actual->getBody());
        $this->assertEquals($now->addDays(30)->toDateTimeString(), $actual->getExpiredAt());
        $this->assertNull($actual->getReceivedAt());
    }

    /**
     * @test
     */
    public function getUnopenedMessageCount_未読メッセージの取得(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $userId = $usrUser->getId();
        $unopenedMessageCount = 1;
        $now = CarbonImmutable::now();
        UsrMessage::factory()
            ->set('usr_user_id', $userId)
            ->set('mng_message_id', 'message_1')
            ->set('opened_at', '2020-01-01 00:00:00')
            ->set('received_at', '2020-01-10 00:00:00')
            ->set('expired_at', '2020-01-14 23:59:59')
            ->create();
        UsrMessage::factory()
            ->set('usr_user_id', $userId)
            ->set('mng_message_id', 'message_1')
            ->set('received_at', '2020-01-10 00:00:00')
            ->set('expired_at', '2020-01-14 23:59:59')
            ->create();
        UsrMessage::factory()
            ->set('usr_user_id', $userId)
            ->set('mng_message_id', 'message_1')
            ->set('opened_at', '2020-01-01 00:00:00')
            ->set('expired_at', '2020-01-14 23:59:59')
            ->create();
        UsrMessage::factory()
            ->set('usr_user_id', $userId)
            ->set('mng_message_id', 'message_1')
            ->set('expired_at', '2020-01-14 23:59:59')
            ->create();
        UsrMessage::factory()
            ->set('usr_user_id', $userId)
            ->set('mng_message_id', 'message_2')
            ->create();

        $gameStartAt = CarbonImmutable::parse($usrUser->getGameStartAt());
        $actual = $this->usrMessageService->getUnopenedMessageCount($usrUser->getId(), $now, Language::Ja->value, $gameStartAt);

        $this->assertEquals($unopenedMessageCount, $actual);
    }

    /**
     * @test
     * @dataProvider addNewMessagesAccountCreatedData
     */
    public function addNewMessages_アカウント作成日時条件のパターンチェック(
        ?string $accountCreatedStartAt,
        ?string $accountCreatedEndAt,
    ): void {
        // Setup
        $usrUser = $this->createUsrUser();
        CarbonImmutable::setTestNow('2020-02-15 00:00:00');
        $now = CarbonImmutable::now();

        // gameStartAtを設定
        $usrUser->game_start_at = '2020-01-01 00:00:00';
        $usrUser->save();

        UsrUserLogin::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'first_login_at' => '2020-01-01 00:00:00',
        ]);

        $mngMessage1 = MngMessage::factory()
            ->set('id', 'message_1')
            ->set('start_at', '2020-02-01 00:00:00')
            ->set('expired_at', '2020-03-31 23:59:59')
            ->set('account_created_start_at', $accountCreatedStartAt)
            ->set('account_created_end_at', $accountCreatedEndAt)
            ->set('add_expired_days', 7)
            ->create();
        MngMessageI18n::factory()->create([
            'mng_message_id' => 'message_1',
            'language' => Language::Ja->value,
            'title' => 'タイトル1',
            'body' => '本文1',
        ]);

        // Exercise
        $gameStartAt = CarbonImmutable::parse($usrUser->getGameStartAt());
        $this->usrMessageService->addNewMessages($usrUser->getId(), $now, Language::Ja->value, $gameStartAt);
        $this->saveAll();

        // Verify
        $usrMessageResult = $this->usrMessageRepository->getReceivableList($usrUser->getId(), CarbonImmutable::now());

        //  メッセージが登録されている
        $this->assertCount(1, $usrMessageResult);
        //  登録済みメッセージのチェック
        /** @var UsrMessage $resultUsrMessage */
        $resultUsrMessage = $usrMessageResult->first();
        $this->assertEquals($mngMessage1->id, $resultUsrMessage->getMngMessageId());
        $this->assertEquals(MessageSource::MNG_MESSAGE->value, $resultUsrMessage->getMessageSource());
        $this->assertEquals($mngMessage1->opened_at, $resultUsrMessage->getOpenedAt());
        $this->assertEquals($mngMessage1->received_at, $resultUsrMessage->getReceivedAt());
        $this->assertEquals($now->addDays(7), $resultUsrMessage->getExpiredAt());
    }

    /**
     * @return array
     */
    public static function addNewMessagesAccountCreatedData(): array
    {
        return [
            '設定なし' => ['2020-01-01 00:00:00', '2038-01-01 00:00:00'],
            '開始日のみ' => ['2020-01-01 00:00:00', '2038-01-01 00:00:00'],
            '終了日のみ' => ['2020-01-01 00:00:00', '2020-01-01 00:00:00'],
            '両方' => ['2019-12-31 00:00:00', '2020-01-02 00:00:00'],
        ];
    }

    /**
     * @test
     * @dataProvider addNewMessagesAccountNotCreatedData
     */
    public function addNewMessages_アカウント作成日時条件のパターンでデータが作られないことをチェック(
        string $accountCreatedAt,
        ?string $accountCreatedStartAt,
        ?string $accountCreatedEndAt,
    ): void {
        // Setup
        $usrUser = $this->createUsrUser();
        CarbonImmutable::setTestNow('2020-02-15 00:00:00');
        $now = CarbonImmutable::now();

        // gameStartAtを設定（テストパラメータから）
        $usrUser->game_start_at = $accountCreatedAt;
        $usrUser->save();

        UsrUserLogin::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'first_login_at' => $accountCreatedAt,
        ]);
        MngMessage::factory()
            ->set('id', 'message_1')
            ->set('start_at', '2020-02-01 00:00:00')
            ->set('expired_at', '2020-03-31 23:59:59')
            ->set('account_created_start_at', $accountCreatedStartAt)
            ->set('account_created_end_at', $accountCreatedEndAt)
            ->set('add_expired_days', 7)
            ->create();
        MngMessageI18n::factory()->create([
            'mng_message_id' => 'message_1',
            'language' => Language::Ja->value,
            'title' => 'タイトル1',
            'body' => '本文1',
        ]);

        // Exercise
        $gameStartAt = CarbonImmutable::parse($usrUser->getGameStartAt());
        $this->usrMessageService->addNewMessages($usrUser->getId(), $now, Language::Ja->value, $gameStartAt);
        $this->saveAll();

        // Verify
        $usrMessageResult = $this->usrMessageRepository->getReceivableList($usrUser->getId(), CarbonImmutable::now());

        //  メッセージが登録されていない
        $this->assertCount(0, $usrMessageResult);
    }

    /**
     * @return array
     */
    public static function addNewMessagesAccountNotCreatedData(): array
    {
        return [
            '開始日のみ' => ['2019-12-31 23:59:59', '2020-01-01 00:00:00', '2038-01-01 00:00:00'],
            '終了日のみ' => ['2020-01-01 00:00:01', '2019-12-31 00:00:00', '2020-01-01 00:00:00'],
            '両方(条件範囲より過去)' => ['2019-12-30 23:59:59', '2019-12-31 00:00:00', '2020-01-02 00:00:00'],
            '両方(条件範囲より未来)' => ['2020-01-02 00:00:01', '2019-12-31 00:00:00', '2020-01-02 00:00:00'],
        ];
    }

    /**
     * @test
     * @dataProvider addNewMessagesExpiredAtData
     */
    public function addNewMessages_受け取り期限日時加算日数パターンチェック(
        ?string $expiredAt,
        int $addExpiredAt,
    ): void {
        // Setup
        $usrUser = $this->createUsrUser();
        CarbonImmutable::setTestNow('2020-03-15 00:00:00');
        $now = CarbonImmutable::now();

        // gameStartAtを設定
        $usrUser->game_start_at = '2020-01-01 00:00:00';
        $usrUser->save();

        UsrUserLogin::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'first_login_at' => '2020-01-01 00:00:00',
        ]);
        $mngMessage1 = MngMessage::factory()
            ->set('id', 'message_1')
            ->set('start_at', '2020-03-01 00:00:00')
            ->set('expired_at', $expiredAt)
            ->set('add_expired_days', $addExpiredAt)
            ->create();
        MngMessageI18n::factory()->create([
            'mng_message_id' => 'message_1',
            'language' => Language::Ja->value,
            'title' => 'タイトル1',
            'body' => '本文1',
        ]);

        // Exercise
        $gameStartAt = CarbonImmutable::parse($usrUser->getGameStartAt());
        $this->usrMessageService->addNewMessages($usrUser->getId(), $now, Language::Ja->value, $gameStartAt);
        $this->saveAll();

        // Verify
        $usrMessageResult = $this->usrMessageRepository->getReceivableList($usrUser->getId(), CarbonImmutable::now());

        $this->assertCount(1, $usrMessageResult);
        /** @var UsrMessage $resultUsrMessage */
        $resultUsrMessage = $usrMessageResult->first();
        // 受け取り期限日時に絞ってチェックする
        $this->assertEquals($now->addDays($addExpiredAt), $resultUsrMessage->getExpiredAt());
    }

    /**
     * @return array[]
     */
    public static function addNewMessagesExpiredAtData(): array
    {
        return [
            '受け取り期限設定あり、加算日数が7日' => ['2020-03-31 23:59:59', 7],
            '受け取り期限設定あり、加算日数が30日' => ['2020-03-31 23:59:59', 30],
            '受け取り期限設定あり、加算日数が100日' => ['2020-03-31 23:59:59', 100],
            '受け取り期限設定なし、加算日数が7日' => ['2038-01-01 00:00:00', 7],
            '受け取り期限設定なし、加算日数が30日' => ['2038-01-01 00:00:00', 30],
            '受け取り期限設定なし、加算日数が10日' => ['2038-01-01 00:00:00', 100],
        ];
    }

    public function test_addNewSystemMessages_登録のチェック(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        CarbonImmutable::setTestNow('2020-01-15 00:00:00');
        $now = CarbonImmutable::now();

        // gameStartAtを設定
        $usrUser->game_start_at = $now->subDays(2)->toDateTimeString();
        $usrUser->save();

        UsrUserLogin::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'first_login_at' => $now->subDays(2)->toDateTimeString(),
        ]);

        // 登録済み(全体配布、既読済み)
        MngMessage::factory()
            ->set('id', 'message_1')
            ->set('start_at', '2020-01-01 00:01:00')
            ->create();
        MngMessageI18n::factory()->create([
            'mng_message_id' => 'message_1',
            'language' => Language::Ja->value,
            'title' => 'タイトル1',
            'body' => '本文1',
        ]);
        $usrMessage1 = UsrMessage::factory()
            ->set('usr_user_id', $usrUser->getId())
            ->set('mng_message_id', 'message_1')
            ->set('message_source', MessageSource::MNG_MESSAGE->value)
            ->set('received_at', null)
            ->set('opened_at', '2020-01-03 11:10:00')
            ->create();
        // 登録済み(個別配布、未既読)
        MngMessage::factory()
            ->set('id', 'message_2')
            ->set('start_at', '2020-01-01 00:01:00')
            ->set('type', MngMessageType::INDIVIDUAL->value)
            ->set('add_expired_days', 7)
            ->create();
        MngMessageI18n::factory()->create([
            'mng_message_id' => 'message_2',
            'language' => Language::Ja->value,
            'title' => 'タイトル2',
            'body' => '本文2',
        ]);
        UsrTemporaryIndividualMessage::factory()
            ->set('usr_user_id', $usrUser->getId())
            ->set('mng_message_id', 'message_2')
            ->create();

        // Exercise
        // システムメッセージを登録
        $this->usrMessageService->addNewSystemMessage(
            $usrUser->getId(),
            null,
            CarbonImmutable::parse('2020-01-24 10:00:00'),
            new Test1Reward(
                RewardType::COIN->value,
                null,
                100,
            ),
            'test_title',
            'test_body'
        );
        $gameStartAt = CarbonImmutable::parse($usrUser->getGameStartAt());
        $this->usrMessageService->addNewMessages($usrUser->getId(), $now, Language::Ja->value, $gameStartAt);
        $this->saveAll();

        // Verify
        $usrMessageResult = $this->usrMessageRepository->getReceivableList($usrUser->getId(), CarbonImmutable::now())
            ->keyBy(fn($row) => $row->getMngMessageId());

        //  メッセージが3件
        $this->assertCount(3, $usrMessageResult);
        //  登録済みメッセージの中身に変更がないことをチェック
        $resultUsrMessage1 = $usrMessageResult->get($usrMessage1->getMngMessageId());
        $this->assertEquals($usrMessage1->getMngMessageId(), $resultUsrMessage1->getMngMessageId());
        $this->assertEquals($usrMessage1->getMessageSource(), $resultUsrMessage1->getMessageSource());
        $this->assertEquals($usrMessage1->getOpenedAt(), $resultUsrMessage1->getOpenedAt());
        $this->assertEquals($usrMessage1->getReceivedAt(), $resultUsrMessage1->getReceivedAt());
        $this->assertEquals($usrMessage1->getExpiredAt(), $resultUsrMessage1->getExpiredAt());
        /** @var UsrMessage $resultUsrMessage2 */
        $resultUsrMessage2 = $usrMessageResult->get('message_2');
        $this->assertEquals('message_2', $resultUsrMessage2->getMngMessageId());
        $this->assertEquals(MessageSource::MNG_MESSAGE->value, $resultUsrMessage2->getMessageSource());
        $this->assertEquals(null, $resultUsrMessage2->getOpenedAt());
        $this->assertEquals(null, $resultUsrMessage2->getReceivedAt());
        // addExpiredDaysが加算された状態として登録
        $this->assertEquals('2020-01-22 00:00:00', $resultUsrMessage2->getExpiredAt());
        /** @var UsrMessage $resultUsrMessage3 */
        $resultUsrMessage3 = $usrMessageResult->get(null);
        $this->assertEquals('Test1Reward', $resultUsrMessage3->getMessageSource());
        $this->assertEquals(RewardType::COIN->value, $resultUsrMessage3->getResourceType());
        $this->assertNull($resultUsrMessage3->getResourceId());
        $this->assertEquals(100, $resultUsrMessage3->getResourceAmount());
        $this->assertEquals('test_title', $resultUsrMessage3->getTitle());
        $this->assertEquals('test_body', $resultUsrMessage3->getBody());
        $this->assertEquals(null, $resultUsrMessage3->getOpenedAt());
        $this->assertEquals(null, $resultUsrMessage3->getReceivedAt());
        // addExpiredDaysが加算された状態として登録
        $this->assertEquals('2020-01-24 10:00:00', $resultUsrMessage3->getExpiredAt());
    }

    /**
     * gameStartAtがfirstLoginAtと異なる場合にgameStartAtが使用されることをテスト
     */
    public function test_addNewMessages_gameStartAtがfirstLoginAtと異なる場合の確認(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        CarbonImmutable::setTestNow('2020-02-15 00:00:00');
        $now = CarbonImmutable::now();

        // gameStartAtとfirstLoginAtを異なる日時に設定
        $usrUser->game_start_at = '2020-01-01 00:00:00'; // gameStartAtは2020-01-01
        $usrUser->save();

        UsrUserLogin::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'first_login_at' => '2020-01-10 00:00:00', // firstLoginAtは2020-01-10（異なる）
        ]);

        // gameStartAt(2020-01-01)が条件範囲内になるメッセージ
        $mngMessage1 = MngMessage::factory()
            ->set('id', 'message_1')
            ->set('start_at', '2020-02-01 00:00:00')
            ->set('expired_at', '2020-03-31 23:59:59')
            ->set('account_created_start_at', '2019-12-31 00:00:00') // gameStartAtより前
            ->set('account_created_end_at', '2020-01-02 00:00:00')   // gameStartAtより後
            ->set('add_expired_days', 7)
            ->create();
        MngMessageI18n::factory()->create([
            'mng_message_id' => 'message_1',
            'language' => Language::Ja->value,
            'title' => 'タイトル1',
            'body' => '本文1',
        ]);

        // firstLoginAt(2020-01-10)だと条件範囲外になるメッセージ
        $mngMessage2 = MngMessage::factory()
            ->set('id', 'message_2')
            ->set('start_at', '2020-02-01 00:00:00')
            ->set('expired_at', '2020-03-31 23:59:59')
            ->set('account_created_start_at', '2020-01-05 00:00:00') // firstLoginAtより前
            ->set('account_created_end_at', '2020-01-08 00:00:00')   // firstLoginAtより前（gameStartAtは範囲外）
            ->set('add_expired_days', 7)
            ->create();
        MngMessageI18n::factory()->create([
            'mng_message_id' => 'message_2',
            'language' => Language::Ja->value,
            'title' => 'タイトル2',
            'body' => '本文2',
        ]);

        // Exercise
        $gameStartAt = CarbonImmutable::parse($usrUser->getGameStartAt());
        $this->usrMessageService->addNewMessages($usrUser->getId(), $now, Language::Ja->value, $gameStartAt);
        $this->saveAll();

        // Verify
        $usrMessageResult = $this->usrMessageRepository->getReceivableList($usrUser->getId(), CarbonImmutable::now())
            ->keyBy(fn($row) => $row->getMngMessageId());

        // gameStartAtが使用されているため、message_1のみ登録されているはず
        $this->assertCount(1, $usrMessageResult);
        $this->assertArrayHasKey('message_1', $usrMessageResult->toArray());
        $this->assertArrayNotHasKey('message_2', $usrMessageResult->toArray());

        $resultUsrMessage1 = $usrMessageResult->get('message_1');
        $this->assertEquals('message_1', $resultUsrMessage1->getMngMessageId());
        $this->assertEquals(MessageSource::MNG_MESSAGE->value, $resultUsrMessage1->getMessageSource());
    }

    public function test_addNewSystemMessage_usr_messages_message_sourceに保存する経緯情報の確認(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        CarbonImmutable::setTestNow('2020-01-15 00:00:00');
        $now = CarbonImmutable::now();

        // データプロバイダーパターン
        $testCases = [
            'prefixなし_trigger_valueあり' => [
                'prefixMessageSource' => null,
                'triggerValue' => 'gacha-123',
                'expectedMessageSource' => 'Test1Reward:gacha-123',
            ],
            'prefixなし_trigger_value空文字' => [
                'prefixMessageSource' => null,
                'triggerValue' => '',
                'expectedMessageSource' => 'Test1Reward', // 空文字列はStringUtil::isSpecified()でフィルタされる
            ],
            'RESOURCE_LIMIT_REACHED_trigger_valueあり' => [
                'prefixMessageSource' => MessageSource::RESOURCE_LIMIT_REACHED->value,
                'triggerValue' => 'quest-reward-456',
                'expectedMessageSource' => 'ResourceLimitReached:Test1Reward:quest-reward-456',
            ],
            'RESOURCE_LIMIT_REACHED_trigger_value空文字' => [
                'prefixMessageSource' => MessageSource::RESOURCE_LIMIT_REACHED->value,
                'triggerValue' => '',
                'expectedMessageSource' => 'ResourceLimitReached:Test1Reward', // 空文字列はフィルタされる
            ],
            'カスタムprefix' => [
                'prefixMessageSource' => 'CUSTOM_PREFIX',
                'triggerValue' => 'custom-value',
                'expectedMessageSource' => 'CUSTOM_PREFIX:Test1Reward:custom-value',
            ],
        ];

        foreach ($testCases as $testName => $testCase) {
            // Exercise
            $usrMessage = $this->usrMessageService->addNewSystemMessage(
                $usrUser->getId(),
                'reward-group-' . $testName,
                $now->addDays(7),
                new Test1Reward(
                    RewardType::COIN->value,
                    null,
                    100,
                    $testCase['triggerValue']
                ),
                'Test Title',
                'Test Body',
                $testCase['prefixMessageSource']
            );

            // Verify
            $this->assertEquals(
                $testCase['expectedMessageSource'],
                $usrMessage->getMessageSource(),
                "Test case '{$testName}' failed. Expected message_source mismatch."
            );
        }
    }

    public function testAddNewMessage_ランクマッチ未受取報酬がメッセージ登録される(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $now = $this->fixTime('2025-09-08 00:00:00');
        UsrUserLogin::factory()->create([
            'usr_user_id' => $usrUserId,
            'first_login_at' => $now->toDateTimeString(),
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        // シーズンデータ作成
        $seasons = [];
        $seasonCount = 3;
        foreach (range(1, $seasonCount) as $i) {
            $startAt = $now->clone()->subWeeks($seasonCount - $i)->addHours(12);
            $id = $this->pvpService->getCurrentSeasonId($startAt);
            $seasons[$i] = SysPvpSeason::factory()->create([
                'id' => $id,
                'start_at' => $startAt->toDateTimeString(),
                'end_at' => $now->clone()->subWeeks($seasonCount - $i + 1)->subSecond()->toDateTimeString(),
                'closed_at' => $now->clone()->subWeeks($seasonCount - $i + 1)->addHours(12)->subSecond()->toDateTimeString(),
            ]);
        }
        $lastPlayedSeason = $seasons[1];

        // 最終プレイシーズンのUsrPvp
        $lastPlayedAt = CarbonImmutable::parse($lastPlayedSeason->end_at)->subDay()->toDateTimeString();
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $lastPlayedSeason->getId(),
            'pvp_rank_class_type' => PvpRankClassType::GOLD->value,
            'pvp_rank_class_level' => 2,
            'last_played_at' => $lastPlayedAt,
            'ranking' => 11,
            'is_season_reward_received' => false,
        ]);

        // 報酬グループ・報酬データ作成用ヘルパー
        $createRewardGroupAndRewards = function($season, $rankType, $rankLevel, $amounts, $category = PvpRewardCategory::RANK_ClASS->value, $condValue = null) {
            try {
                $mstPvpRank = MstPvpRank::factory()->create([
                    'rank_class_type' => $rankType,
                    'rank_class_level' => $rankLevel,
                ])->toEntity();
            } catch (\Exception $e) {
                $mstPvpRank = MstPvpRank::query()
                    ->where('rank_class_type', $rankType)
                    ->where('rank_class_level', $rankLevel)
                    ->firstOrFail()
                    ->toEntity();
            }
            $id = $category;
            $id .= $condValue ?? ($rankType . $rankLevel);
            $group = MstPvpRewardGroup::factory()->create([
                'id' => $id,
                'mst_pvp_id' => $season->id,
                'reward_category' => $category,
                'condition_value' => $condValue ?? $mstPvpRank->getId(),
            ])->toEntity();
            foreach ($amounts as $amt) {
                MstPvpReward::factory()->create([
                    'mst_pvp_reward_group_id' => $group->getId(),
                    'resource_amount' => $amt,
                ]);
            }
        };

        $pvpCacheService = $this->app->make(PvpCacheService::class);
        $pvpCacheService->addRankingScore(
            $lastPlayedSeason->getId(),
            $usrUserId,
            1000
        );

        // 最終プレイシーズンのランク報酬・ランキング報酬
        $createRewardGroupAndRewards($seasons[1], PvpRankClassType::GOLD->value, 2, [1000, 900]);
        $createRewardGroupAndRewards($seasons[1], PvpRankClassType::GOLD->value, 2, [100, 200], PvpRewardCategory::RANKING->value, '100');
        // シーズン2のランク報酬
        $createRewardGroupAndRewards($seasons[2], PvpRankClassType::SILVER->value, 1, [500]);
        // シーズン3のランク報酬
        $createRewardGroupAndRewards($seasons[3], PvpRankClassType::BRONZE->value, 1, [100]);

        // Exercise
        $gameStartAt = CarbonImmutable::parse($usrUser->getGameStartAt());
        $this->usrMessageService->addNewMessages($usrUserId, $now, Language::Ja->value, $gameStartAt);
        $this->saveAll();

        // Verify
        $usrMessageRepository = $this->app->make(UsrMessageRepository::class);
        $messages = $usrMessageRepository->getByUserId($usrUserId);
        
        $rankMessages = $messages->filter(function ($message) {
            return $message->getTitle() === MessageConstant::PVP_RANK_REWARD_TITLE;
        })->sortByDesc(fn ($message) => $message->getResourceAmount())->values();
        $this->assertCount(2, $rankMessages);
        $this->assertEquals(1000, $rankMessages[0]->getResourceAmount());
        $this->assertEquals(900, $rankMessages[1]->getResourceAmount());
        $mstPvpRewardGroupId = PvpRewardCategory::RANK_ClASS->value . PvpRankClassType::GOLD->value . '2';
        $testGroupId = $mstPvpRewardGroupId . '_' . $usrPvp->getSysPvpSeasonId();
        $this->assertEquals($testGroupId, $rankMessages[0]->getRewardGroupId());
        $this->assertEquals($testGroupId, $rankMessages[1]->getRewardGroupId());

        $rankingMessages = $messages->filter(function ($message) {
            return $message->getTitle() === MessageConstant::PVP_RANKING_REWARD_TITLE;
        })->sortByDesc(fn ($message) => $message->getResourceAmount())->values();
        $this->assertCount(2, $rankingMessages);
        $this->assertEquals(200, $rankingMessages[0]->getResourceAmount());
        $this->assertEquals(100, $rankingMessages[1]->getResourceAmount());
        $mstPvpRewardGroupId = PvpRewardCategory::RANKING->value . '100';
        $testGroupId = $mstPvpRewardGroupId . '_' . $usrPvp->getSysPvpSeasonId();
        $this->assertEquals($testGroupId, $rankingMessages[0]->getRewardGroupId());
        $this->assertEquals($testGroupId, $rankingMessages[1]->getRewardGroupId());
    }

    public function testAddNewMessage_1シーズン目プレイ済み2シーズン目未プレイで集計期間中に1シーズン目報酬がメッセージ付与される(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        // 月曜の0時に固定
        $now = $this->fixTime('2025-09-15 00:00:00');
        UsrUserLogin::factory()->create([
            'usr_user_id' => $usrUserId,
            'first_login_at' => $now->toDateTimeString(),
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        // シーズンデータ作成（1シーズン目と2シーズン目）
        $season1StartAt = $now->clone()->subWeeks(2);
        $season1EndAt = $now->clone()->subWeeks(1)->subSecond();
        $season1ClosedAt = $now->clone()->subWeeks(1)->addHours(12)->subSecond();
        $season1Id = $this->pvpService->getCurrentSeasonId($season1StartAt);
        $season1 = SysPvpSeason::factory()->create([
            'id' => $season1Id,
            'start_at' => $season1StartAt->toDateTimeString(),
            'end_at' => $season1EndAt->toDateTimeString(),
            'closed_at' => $season1ClosedAt->toDateTimeString(),
        ]);

        $season2StartAt = $now->clone()->subWeeks(1);
        $season2EndAt = $now->clone()->subSecond();
        $season2ClosedAt = $now->clone()->addHours(12)->subSecond();
        $season2Id = $this->pvpService->getCurrentSeasonId($season2StartAt);
        $season2 = SysPvpSeason::factory()->create([
            'id' => $season2Id,
            'start_at' => $season2StartAt->toDateTimeString(),
            'end_at' => $season2EndAt->toDateTimeString(),
            'closed_at' => $season2ClosedAt->toDateTimeString(),
        ]);

        // 1シーズン目のUsrPvp（プレイ済み）
        $season1LastPlayedAt = CarbonImmutable::parse($season1->end_at)->subDay()->toDateTimeString();
        $usrPvpSeason1 = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $season1->getId(),
            'pvp_rank_class_type' => PvpRankClassType::SILVER->value,
            'pvp_rank_class_level' => 3,
            'last_played_at' => $season1LastPlayedAt,
            'ranking' => 15,
            'is_season_reward_received' => false,
        ]);

        // 2シーズン目のUsrPvpは作成しない（未プレイ）

        // 報酬グループ・報酬データ作成用ヘルパー
        $createRewardGroupAndRewards = function($season, $rankType, $rankLevel, $amounts, $category = PvpRewardCategory::RANK_ClASS->value, $condValue = null) {
            try {
                $mstPvpRank = MstPvpRank::factory()->create([
                    'rank_class_type' => $rankType,
                    'rank_class_level' => $rankLevel,
                ])->toEntity();
            } catch (\Exception $e) {
                $mstPvpRank = MstPvpRank::query()
                    ->where('rank_class_type', $rankType)
                    ->where('rank_class_level', $rankLevel)
                    ->firstOrFail()
                    ->toEntity();
            }
            $group = MstPvpRewardGroup::factory()->create([
                'mst_pvp_id' => $season->id,
                'reward_category' => $category,
                'condition_value' => $condValue ?? $mstPvpRank->getId(),
            ])->toEntity();
            foreach ($amounts as $amt) {
                MstPvpReward::factory()->create([
                    'mst_pvp_reward_group_id' => $group->getId(),
                    'resource_amount' => $amt,
                ]);
            }
        };

        $pvpCacheService = $this->app->make(PvpCacheService::class);
        $pvpCacheService->addRankingScore(
            $season1->getId(),
            $usrUserId,
            1500
        );

        // 1シーズン目のランク報酬・ランキング報酬
        $createRewardGroupAndRewards($season1, PvpRankClassType::SILVER->value, 3, [800, 600]);
        $createRewardGroupAndRewards($season1, PvpRankClassType::SILVER->value, 3, [150, 120], PvpRewardCategory::RANKING->value, '20');

        // Exercise（2シーズン目の集計期間中にログイン）
        $gameStartAt = CarbonImmutable::parse($usrUser->getGameStartAt());
        $this->usrMessageService->addNewMessages($usrUserId, $now, Language::Ja->value, $gameStartAt);
        $this->saveAll();

        // Verify
        $usrMessageRepository = $this->app->make(UsrMessageRepository::class);
        $messages = $usrMessageRepository->getByUserId($usrUserId);
        
        // 1シーズン目の報酬が間隔が開いているため、メッセージに付与される
        $rankMessages = $messages->filter(function ($message) {
            return $message->getTitle() === MessageConstant::PVP_RANK_REWARD_TITLE;
        })->sortByDesc(fn ($message) => $message->getResourceAmount())->values();
        $this->assertCount(2, $rankMessages);
        $this->assertEquals(800, $rankMessages[0]->getResourceAmount());
        $this->assertEquals(600, $rankMessages[1]->getResourceAmount());

        $rankingMessages = $messages->filter(function ($message) {
            return $message->getTitle() === MessageConstant::PVP_RANKING_REWARD_TITLE;
        })->sortByDesc(fn ($message) => $message->getResourceAmount())->values();
        $this->assertCount(2, $rankingMessages);
        $this->assertEquals(150, $rankingMessages[0]->getResourceAmount());
        $this->assertEquals(120, $rankingMessages[1]->getResourceAmount());

        // UsrPvpの報酬受取済みフラグが更新されている
        $usrPvpRepository = $this->app->make(UsrPvpRepository::class);
        $updatedUsrPvp = $usrPvpRepository->getBySysPvpSeasonId($usrUserId, $season1->getId());
        $this->assertTrue($updatedUsrPvp->isSeasonRewardReceived());
    }

    public function testAddNewMessage_1シーズン目プレイ済み2シーズン目開催中では何も付与されない(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();

        // シーズンデータ作成（1シーズン目と2シーズン目）
        $date = CarbonImmutable::parse('2025-09-15 00:00:00');
        $season1StartAt = $date->clone()->subWeeks(2);
        $season1EndAt = $date->clone()->subWeeks(1)->subSecond();
        $season1ClosedAt = $date->clone()->subWeeks(1)->addHours(12)->subSecond();
        $season1Id = $this->pvpService->getCurrentSeasonId($season1StartAt);
        $season1 = SysPvpSeason::factory()->create([
            'id' => $season1Id,
            'start_at' => $season1StartAt->toDateTimeString(),
            'end_at' => $season1EndAt->toDateTimeString(),
            'closed_at' => $season1ClosedAt->toDateTimeString(),
        ]);

        $season2StartAt = $date->clone()->subWeeks(1);
        $season2EndAt = $date->clone()->subSecond();
        $season2ClosedAt = $date->clone()->addHours(12)->subSecond();
        $season2Id = $this->pvpService->getCurrentSeasonId($season2StartAt);
        $season2 = SysPvpSeason::factory()->create([
            'id' => $season2Id,
            'start_at' => $season2StartAt->toDateTimeString(),
            'end_at' => $season2EndAt->toDateTimeString(),
            'closed_at' => $season2ClosedAt->toDateTimeString(),
        ]);

        // 月曜の0時に固定
        $now = $this->fixTime($date->subDay()->toDateTimeString());
        UsrUserLogin::factory()->create([
            'usr_user_id' => $usrUserId,
            'first_login_at' => $now->toDateTimeString(),
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);


        // 1シーズン目のUsrPvp（プレイ済み）
        $season1LastPlayedAt = CarbonImmutable::parse($season1->end_at)->subDay()->toDateTimeString();
        $usrPvpSeason1 = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $season1->getId(),
            'pvp_rank_class_type' => PvpRankClassType::SILVER->value,
            'pvp_rank_class_level' => 3,
            'last_played_at' => $season1LastPlayedAt,
            'ranking' => 15,
            'is_season_reward_received' => false,
        ]);

        // 2シーズン目のUsrPvpは作成しない（未プレイ）

        // 報酬グループ・報酬データ作成用ヘルパー
        $createRewardGroupAndRewards = function($season, $rankType, $rankLevel, $amounts, $category = PvpRewardCategory::RANK_ClASS->value, $condValue = null) {
            try {
                $mstPvpRank = MstPvpRank::factory()->create([
                    'rank_class_type' => $rankType,
                    'rank_class_level' => $rankLevel,
                ])->toEntity();
            } catch (\Exception $e) {
                $mstPvpRank = MstPvpRank::query()
                    ->where('rank_class_type', $rankType)
                    ->where('rank_class_level', $rankLevel)
                    ->firstOrFail()
                    ->toEntity();
            }
            $group = MstPvpRewardGroup::factory()->create([
                'mst_pvp_id' => $season->id,
                'reward_category' => $category,
                'condition_value' => $condValue ?? $mstPvpRank->getId(),
            ])->toEntity();
            foreach ($amounts as $amt) {
                MstPvpReward::factory()->create([
                    'mst_pvp_reward_group_id' => $group->getId(),
                    'resource_amount' => $amt,
                ]);
            }
        };

        $pvpCacheService = $this->app->make(PvpCacheService::class);
        $pvpCacheService->addRankingScore(
            $season1->getId(),
            $usrUserId,
            1500
        );

        // 1シーズン目のランク報酬・ランキング報酬
        $createRewardGroupAndRewards($season1, PvpRankClassType::SILVER->value, 3, [800, 600]);
        $createRewardGroupAndRewards($season1, PvpRankClassType::SILVER->value, 3, [150, 120], PvpRewardCategory::RANKING->value, '20');

        // Exercise（2シーズン目の集計期間中にログイン）
        $gameStartAt = CarbonImmutable::parse($usrUser->getGameStartAt());
        $this->usrMessageService->addNewMessages($usrUserId, $now, Language::Ja->value, $gameStartAt);
        $this->saveAll();

        // Verify
        $usrMessageRepository = $this->app->make(UsrMessageRepository::class);
        $messages = $usrMessageRepository->getByUserId($usrUserId);
        
        // メッセージは何も付与されない
        $this->assertCount(0, $messages);

        // UsrPvpの報酬受取済みフラグは更新されない
        $usrPvpRepository = $this->app->make(UsrPvpRepository::class);
        $updatedUsrPvp = $usrPvpRepository->getBySysPvpSeasonId($usrUserId, $season1->getId());
        $this->assertFalse($updatedUsrPvp->isSeasonRewardReceived());
    }
}
