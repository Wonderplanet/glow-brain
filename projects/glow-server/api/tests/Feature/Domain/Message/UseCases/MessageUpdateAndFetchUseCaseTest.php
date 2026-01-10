<?php

namespace Feature\Domain\Message\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use Tests\Support\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Message\Delegator\MessageDelegator;
use App\Domain\Message\Enums\MessageSource;
use App\Domain\Message\Enums\MngMessageType;
use App\Domain\Message\Models\Eloquent\UsrMessage;
use App\Domain\Message\Repositories\UsrMessageRepository;
use App\Domain\Message\UseCases\MessageUpdateAndFetchUseCase;
use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Pvp\Enums\PvpRewardCategory;
use App\Domain\Pvp\Models\SysPvpSeason;
use App\Domain\Pvp\Models\UsrPvp;
use App\Domain\Pvp\Services\PvpCacheService;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mng\Models\MngMessage;
use App\Domain\Resource\Mng\Models\MngMessageI18n;
use App\Domain\Resource\Mng\Models\MngMessageReward;
use App\Domain\Resource\Mst\Models\MstPvpRank;
use App\Domain\Resource\Mst\Models\MstPvpReward;
use App\Domain\Resource\Mst\Models\MstPvpRewardGroup;
use App\Domain\User\Models\UsrUserLogin;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Redis;
use Tests\Feature\Domain\Reward\Test1Reward;
use Tests\TestCase;

class MessageUpdateAndFetchUseCaseTest extends TestCase
{
    private MessageUpdateAndFetchUseCase $useCase;
    private UsrMessageRepository $usrMessageRepository;
    private PvpCacheService $pvpCacheService;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = app()->make(MessageUpdateAndFetchUseCase::class);
        $this->usrMessageRepository = app()->make(UsrMessageRepository::class);
        $this->pvpCacheService = app()->make(PvpCacheService::class);
    }

    public function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        Redis::flushall();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function exec_一覧取得(): void
    {
        // Setup
        CarbonImmutable::setTestNow('2020-01-15 00:00:00');
        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        UsrUserLogin::factory()->create([
            'usr_user_id' => $currentUser->getUsrUserId(),
            'first_login_at' => '2020-01-01 00:00:00',
        ]);

        //  表示期限がすぎている
        MngMessage::factory()
            ->set('id', 'out1')
            ->set('start_at', '2019-12-01 00:00:00')
            ->set('expired_at', '2019-12-31 23:59:59')
            ->create();
        MngMessageI18n::factory()
            ->set('mng_message_id', 'out1')
            ->set('title', 'テスト タイトル 期間外')
            ->set('body', 'テスト 本文 期間外')
            ->create();
        UsrMessage::factory()
            ->set('usr_user_id', $currentUser->getUsrUserId())
            ->set('mng_message_id', 'out1')
            ->set('expired_at', '2019-12-31 23:59:59')
            ->create();
        //  別ユーザー
        MngMessage::factory()
            ->set('id', 'out2')
            ->set('type', MngMessageType::INDIVIDUAL->value)
            ->set('start_at', '2020-01-01 00:00:00')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        MngMessageI18n::factory()
            ->set('mng_message_id', 'out2')
            ->set('title', 'テスト タイトル 別ユーザー')
            ->set('body', 'テスト 本文 別ユーザー')
            ->create();
        UsrMessage::factory()
            ->set('usr_user_id', fake()->uuid)
            ->set('mng_message_id', 'out2')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        // メッセージのみ 既読済み
        $mngMessage1 = MngMessage::factory()
            ->set('id', 'message_1')
            ->set('start_at', '2020-01-01 00:01:00')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        $mngMessageI18n1 = MngMessageI18n::factory()
            ->set('mng_message_id', 'message_1')
            ->set('title', 'テスト タイトル 既読')
            ->set('body', 'テスト 本文 既読')
            ->create();
        $usrMessage1 = UsrMessage::factory()
            ->set('usr_user_id', $currentUser->getUsrUserId())
            ->set('mng_message_id', 'message_1')
            ->set('opened_at', '2020-01-01 00:00:00')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        // 配布物あり(1種) 受け取り済み
        $mngMessage2 = MngMessage::factory()
            ->set('id', 'message_2')
            ->set('start_at', '2020-01-01 12:00:00')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        $mngMessageReward1 = MngMessageReward::factory()
            ->set('id', 'distribution_1')
            ->set('mng_message_id', 'message_2')
            ->set('resource_type', RewardType::FREE_DIAMOND->value)
            ->set('resource_id', '1')
            ->set('resource_amount', 1)
            ->create();
        $mngMessageI18n2 = MngMessageI18n::factory()
            ->set('mng_message_id', 'message_2')
            ->set('title', 'テスト タイトル 受け取り済み')
            ->set('body', 'テスト 本文 受け取り済み')
            ->create();
        $usrMessage2 = UsrMessage::factory()
            ->set('usr_user_id', $currentUser->getUsrUserId())
            ->set('mng_message_id', 'message_2')
            ->set('opened_at', '2020-01-03 11:10:00')
            ->set('received_at', '2020-01-03 11:10:00')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        // メッセージのみ 未読
        $mngMessage3 = MngMessage::factory()
            ->set('id', 'message_3')
            ->set('start_at', '2020-01-02 00:00:00')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        $mngMessageI18n3 = MngMessageI18n::factory()
            ->set('mng_message_id', 'message_3')
            ->set('title', 'テスト タイトル メッセージ未読')
            ->set('body', 'テスト 本文 メッセージ未読')
            ->create();
        $usrMessage3 = UsrMessage::factory()
            ->set('usr_user_id', $currentUser->getUsrUserId())
            ->set('mng_message_id', 'message_3')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        // 配布物あり(2種) 未受け取り
        $mngMessage4 = MngMessage::factory()
            ->set('id', 'message_4')
            ->set('start_at', '2020-01-02 00:00:00')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        $mngMessageReward2 = MngMessageReward::factory()
            ->set('id', 'distribution_2')
            ->set('mng_message_id', 'message_4')
            ->set('display_order', 2)
            ->set('resource_type', RewardType::FREE_DIAMOND->value)
            ->set('resource_id', '1')
            ->set('resource_amount', 1)
            ->create();
        $mngMessageReward3 = MngMessageReward::factory()
            ->set('id', 'distribution_3')
            ->set('mng_message_id', 'message_4')
            ->set('resource_type', RewardType::STAMINA->value)
            ->set('resource_id', '2')
            ->set('resource_amount', 100)
            ->create();
        $mngMessageI18n4 = MngMessageI18n::factory()
            ->set('mng_message_id', 'message_4')
            ->set('title', 'テスト タイトル 未受け取り')
            ->set('body', 'テスト 本文 未受け取り')
            ->create();
        $usrMessage4 = UsrMessage::factory()
            ->set('usr_user_id', $currentUser->getUsrUserId())
            ->set('mng_message_id', 'message_4')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        // システムメッセージを登録
        $messageDelegator = app()->make(MessageDelegator::class);
        $messageDelegator->addNewSystemMessage(
            $currentUser->getUsrUserId(),
            null,
            CarbonImmutable::parse('2020-01-24 10:00:00'),
            new Test1Reward(
                RewardType::ITEM->value,
                'item1',
                10,
            ),
            'test_title',
            'test_body'
        );

        // Exercise
        $result = $this->useCase->exec($currentUser, 'ja');
        $resultData = $result->messageDataList;

        // Verify
        //  配信開始日時(startAt)の新しい順 > messageIdの昇順でソートされているかチェック
        //  messageIdのみ抽出して想定通りの並びになっているかでチェックする
        $messageIds = $resultData->map(function ($messageData) {
            return $messageData->getMngMessageId();
        })->values()->toArray();
        $this->assertEquals(
            [
                null,
                'message_3',
                'message_4',
                'message_2',
                'message_1',
            ],
            $messageIds
        );
        //  値が想定通りかチェック
        // 未読データチェック
        $row1 = $resultData->filter(function ($messageData) {
            return $messageData->getMngMessageId() === 'message_3';
        })->first();
        $this->assertEquals($mngMessage3->id, $row1->getMngMessageId());
        $this->assertEquals($mngMessage3->start_at, $row1->getStartAt());
        $this->assertNull($row1->getOpenedAt());
        $this->assertNull($row1->getReceivedAt());
        $this->assertEquals($usrMessage3->expired_at, $row1->getExpiredAt());
        $this->assertEmpty($row1->getMessageRewards()->toArray());
        $this->assertEquals($mngMessageI18n3->title, $row1->getTitle());
        $this->assertEquals($mngMessageI18n3->body, $row1->getBody());
        // 未受け取りデータチェック
        $row2 = $resultData->filter(function ($messageData) {
            return $messageData->getMngMessageId() === 'message_4';
        })->first();
        $this->assertEquals($mngMessage4->id, $row2->getMngMessageId());
        $this->assertEquals($mngMessage4->start_at, $row2->getStartAt());
        $this->assertNull($row2->getOpenedAt());
        $this->assertNull($row2->getReceivedAt());
        $this->assertEquals($usrMessage4->getExpiredAt(), $row2->getExpiredAt());
        foreach ($row2->getMessageRewards() as $reward) {
            switch ($reward->getType()) {
                case RewardType::FREE_DIAMOND->value:
                    $this->assertEquals(RewardType::FREE_DIAMOND->value, $reward->getType());
                    $this->assertEquals($mngMessageReward2->resource_id, $reward->getResourceId());
                    $this->assertEquals($mngMessageReward2->resource_amount, $reward->getAmount());
                    break;
                case RewardType::STAMINA->value:
                    $this->assertEquals(RewardType::STAMINA->value, $reward->getType());
                    $this->assertEquals($mngMessageReward3->resource_id, $reward->getResourceId());
                    $this->assertEquals($mngMessageReward3->resource_amount, $reward->getAmount());
                    break;
            }
        }
        $this->assertEquals($mngMessageI18n4->title, $row2->getTitle());
        $this->assertEquals($mngMessageI18n4->body, $row2->getBody());
        // 受け取り済みデータチェック
        $row3 = $resultData->filter(function ($messageData) {
            return $messageData->getMngMessageId() === 'message_2';
        })->first();
        $this->assertEquals($mngMessage2->id, $row3->getMngMessageId());
        $this->assertEquals($mngMessage2->start_at, $row3->getStartAt());
        $this->assertEquals($usrMessage2->getOpenedAt(), $row3->getOpenedAt());
        $this->assertEquals($usrMessage2->getReceivedAt(), $row3->getReceivedAt());
        $this->assertEquals($usrMessage2->getExpiredAt(), $row3->getExpiredAt());
        foreach ($row3->getMessageRewards() as $reward) {
            $this->assertEquals(RewardType::FREE_DIAMOND->value, $reward->getType());
            $this->assertEquals($mngMessageReward1->resource_id, $reward->getResourceId());
            $this->assertEquals($mngMessageReward1->resource_amount, $reward->getAmount());
        }
        $this->assertEquals($mngMessageI18n2->title, $row3->getTitle());
        $this->assertEquals($mngMessageI18n2->body, $row3->getBody());
        // 既読データチェック
        $row4 = $resultData->filter(function ($messageData) {
            return $messageData->getMngMessageId() === 'message_1';
        })->first();
        $this->assertEquals($mngMessage1->id, $row4->getMngMessageId());
        $this->assertEquals($mngMessage1->start_at, $row4->getStartAt());
        $this->assertEquals($usrMessage1->getOpenedAt(), $row4->getOpenedAt());
        $this->assertNull($row4->getReceivedAt());
        $this->assertEquals($usrMessage1->getExpiredAt(), $row4->getExpiredAt());
        $this->assertEmpty($row4->getMessageRewards());
        $this->assertEquals($mngMessageI18n1->title, $row4->getTitle());
        $this->assertEquals($mngMessageI18n1->body, $row4->getBody());
        // システムメッセージのチェック
        $row5 = $resultData->filter(function ($messageData) {
            return $messageData->getMngMessageId() === null;
        })->first();
        $this->assertNull($row5->getMngMessageId());
        $this->assertEquals(CarbonImmutable::now()->toDateTimeString(), $row5->getStartAt());
        $this->assertNull($row5->getOpenedAt());
        $this->assertNull($row5->getReceivedAt());
        $this->assertEquals('2020-01-24 10:00:00', $row5->getExpiredAt());
        foreach ($row5->getMessageRewards() as $reward) {
            $this->assertEquals(RewardType::ITEM->value, $reward->getType());
            $this->assertEquals('item1', $reward->getResourceId());
            $this->assertEquals(10, $reward->getAmount());
        }
        $this->assertEquals('test_title', $row5->getTitle());
        $this->assertEquals('test_body', $row5->getBody());
    }

    /**
     * MessageUpdateAndFetchUseCaseを複数回実行してもPvPシーズン報酬が重複追加されないことを確認
     */
    public function test_exec_PvPシーズン報酬の重複追加が修正されていることを確認(): void
    {
        // 現在時刻を設定（シーズン4の期間中）
        CarbonImmutable::setTestNow('2024-04-15 00:00:00');
        
        // ユーザー作成
        $usrUser = $this->createUsrUser();
        $userId = $usrUser->getId();
        $usrUser->game_start_at = '2024-01-01 00:00:00';
        $usrUser->save();
        
        $currentUser = new CurrentUser($userId);
        
        UsrUserLogin::factory()->create([
            'usr_user_id' => $userId,
            'first_login_at' => '2024-01-01 00:00:00',
        ]);

        // PvPシーズンのセットアップ（過去3シーズン + 現在シーズン）
        // シーズン1（古い）
        SysPvpSeason::factory()->create([
            'id' => 2024001,
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => '2024-01-31 23:59:59',
        ]);

        // シーズン2（ユーザーが参加したシーズン）
        SysPvpSeason::factory()->create([
            'id' => 2024002,
            'start_at' => '2024-02-01 00:00:00',
            'end_at' => '2024-02-29 23:59:59',
        ]);

        // シーズン3（前シーズン）
        SysPvpSeason::factory()->create([
            'id' => 2024003,
            'start_at' => '2024-03-01 00:00:00',
            'end_at' => '2024-03-31 23:59:59',
        ]);

        // シーズン4（現在シーズン）
        SysPvpSeason::factory()->create([
            'id' => 2024004,
            'start_at' => '2024-04-01 00:00:00',
            'end_at' => '2024-04-30 23:59:59',
        ]);

        // PvPランクマスタデータ
        $mstPvpRank = MstPvpRank::factory()->create([
            'rank_class_type' => PvpRankClassType::GOLD->value,
            'rank_class_level' => 2,
        ]);

        // 報酬作成のヘルパー関数
        $createRewardGroupAndRewards = function (SysPvpSeason $season, string $rankClass, int $level, array $amounts, ?PvpRewardCategory $category = null, ?string $condValue = null) use ($mstPvpRank) {
            $category = $category ?? PvpRewardCategory::RANK_ClASS;
            $id = $category->value . '_' . $rankClass . $level . '_' . $season->id;
            $group = MstPvpRewardGroup::factory()->create([
                'id' => $id,
                'mst_pvp_id' => $season->id,
                'reward_category' => $category->value,
                'condition_value' => $condValue ?? $mstPvpRank->id,
            ])->toEntity();
            foreach ($amounts as $amt) {
                MstPvpReward::factory()->create([
                    'mst_pvp_reward_group_id' => $group->getId(),
                    'resource_amount' => $amt,
                ]);
            }
        };

        // シーズン2のPvPシーズンオブジェクトを取得
        $season2 = SysPvpSeason::where('id', 2024002)->first();
        
        // PvPキャッシュサービスでランキングを設定
        $this->pvpCacheService->addRankingScore(2024002, $userId, 50); // 50位のスコア
        
        // シーズン2のランク報酬・ランキング報酬を作成
        $createRewardGroupAndRewards($season2, PvpRankClassType::GOLD->value, 2, [500]);
        $createRewardGroupAndRewards($season2, PvpRankClassType::GOLD->value, 2, [1000], PvpRewardCategory::RANKING, '100');

        // ユーザーのPvP参加履歴（シーズン2のみ参加、シーズン間にギャップあり）
        UsrPvp::factory()->create([
            'usr_user_id' => $userId,
            'sys_pvp_season_id' => 2024002,
            'pvp_rank_class_type' => PvpRankClassType::GOLD->value,
            'pvp_rank_class_level' => 2,
            'ranking' => 50, // ランキング50位
            'is_season_reward_received' => false, // まだ受け取っていない
        ]);

        // ランキングは50位として設定（UsrPvpのrankingフィールドに設定済み）

        // === 1回目のexec実行 ===
        $result1 = $this->useCase->exec($currentUser, 'ja');

        // シーズン2の報酬メッセージが作成されたことを確認
        $messagesAfterFirst = $this->usrMessageRepository->getByUserId($userId);
        $pvpMessages = $messagesAfterFirst->filter(function ($message) {
            $rewardGroupId = $message->getRewardGroupId();
            return $rewardGroupId && str_contains($rewardGroupId, '_2024002'); // シーズン2の報酬
        });

        // シーズン2の報酬が作成されていることを確認
        $this->assertGreaterThan(0, $pvpMessages->count());
        $firstCallMessageCount = $pvpMessages->count();

        // メッセージの内容を確認
        $rewardMessages = $pvpMessages->filter(function ($message) {
            return in_array($message->getResourceType(), [RewardType::FREE_DIAMOND->value, RewardType::COIN->value]);
        });
        $this->assertGreaterThan(0, $rewardMessages->count(), '1回目実行後：報酬が含まれていません');

        // === 2回目のexec実行（ここで重複が発生するはず） ===
        $result2 = $this->useCase->exec($currentUser, 'ja');

        // 2回目実行後のメッセージを取得
        $messagesAfterSecond = $this->usrMessageRepository->getByUserId($userId);
        
        $pvpMessagesAfterSecond = $messagesAfterSecond->filter(function ($message) {
            $rewardGroupId = $message->getRewardGroupId();
            return $rewardGroupId && str_contains($rewardGroupId, '_2024002'); // シーズン2の報酬
        });

        // 2回目の実行でも同じシーズン報酬は重複追加されない
        $this->assertEquals(
            $firstCallMessageCount,
            $pvpMessagesAfterSecond->count(),
        );

        // 同じreward_group_idを持つメッセージが重複していないことを確認
        $groupedMessages = $pvpMessagesAfterSecond->groupBy(function ($message) {
            return $message->getRewardGroupId();
        });
        $duplicatedGroups = $groupedMessages->filter(function ($messages) {
            return $messages->count() > 1;
        });

        $this->assertEquals(
            0,
            $duplicatedGroups->count(),
        );

        // === 3回目のexec実行（修正により重複しないはず） ===
        $result3 = $this->useCase->exec($currentUser, 'ja');

        $messagesAfterThird = $this->usrMessageRepository->getByUserId($userId);
        $pvpMessagesAfterThird = $messagesAfterThird->filter(function ($message) {
            $rewardGroupId = $message->getRewardGroupId();
            return $rewardGroupId && str_contains($rewardGroupId, '_2024002'); // シーズン2の報酬
        });

        // 3回目でも件数は変わらないことを確認
        $this->assertEquals(
            $pvpMessagesAfterSecond->count(),
            $pvpMessagesAfterThird->count(),
        );

        // is_season_reward_receivedフラグの状態を確認
        $usrPvpAfter = UsrPvp::where('usr_user_id', $userId)
            ->where('sys_pvp_season_id', 2024002)
            ->first();
        
        // フラグが正しく更新され、メッセージの重複も防げている
        $this->assertTrue(
            $usrPvpAfter->is_season_reward_received,
        );
    }

    /**
     * 既に配布済みの過去シーズン報酬メッセージの報酬量が増加していないことを確認
     */
    public function test_exec_配布済みメッセージの報酬増加が防止されていることを確認(): void
    {
        // 現在時刻を設定（シーズン4の期間中）
        CarbonImmutable::setTestNow('2024-04-15 00:00:00');
        
        // ユーザー作成
        $usrUser = $this->createUsrUser();
        $userId = $usrUser->getId();
        $usrUser->game_start_at = '2024-01-01 00:00:00';
        $usrUser->save();
        
        $currentUser = new CurrentUser($userId);
        
        UsrUserLogin::factory()->create([
            'usr_user_id' => $userId,
            'first_login_at' => '2024-01-01 00:00:00',
        ]);

        // 既存のテストと同じデータを使用
        $this->setupPvpTestData();
        
        // シーズン2でプレイしたユーザーデータを作成
        UsrPvp::factory()->create([
            'usr_user_id' => $userId,
            'sys_pvp_season_id' => 2,
            'pvp_rank_class_type' => PvpRankClassType::GOLD->value,
            'pvp_rank_class_level' => 2,
            'score' => 1500,
            'is_season_reward_received' => false,
        ]);

        // ランキング情報をキャッシュに設定
        app(PvpCacheService::class)->addRankingScore(2, $userId, 1500);

        // 1回目実行（報酬メッセージが初回作成される）
        $useCase = app(MessageUpdateAndFetchUseCase::class);
        $result1 = $useCase->exec($currentUser, CarbonImmutable::now());
        
        // 作成されたメッセージの詳細を確認
        $messages1 = app(UsrMessageRepository::class)->getByUserId($userId)
            ->filter(function ($message) {
                $rewardGroupId = $message->getRewardGroupId();
                return $rewardGroupId && str_contains($rewardGroupId, '_2');
            });
            
        $originalAmounts = [];
        foreach ($messages1 as $message) {
            $rewardGroupId = $message->getRewardGroupId();
            $amount = $message->getResourceAmount();
            $originalAmounts[$rewardGroupId] = $amount;
        }

        // メッセージを受取済みにする（実際のゲーム運用での状況を模擬）
        foreach ($messages1 as $message) {
            $message->setReceivedAt(CarbonImmutable::now());
        }
        app(UsrMessageRepository::class)->syncModels($messages1);

        // 2回目実行（配布済みメッセージが変更されないことを確認）
        $result2 = $useCase->exec($currentUser, CarbonImmutable::now());
        
        $messages2 = app(UsrMessageRepository::class)->getByUserId($userId)
            ->filter(function ($message) {
                $rewardGroupId = $message->getRewardGroupId();
                return $rewardGroupId && str_contains($rewardGroupId, '_2');
            });
            
        $afterAmounts = [];
        foreach ($messages2 as $message) {
            $rewardGroupId = $message->getRewardGroupId();
            $amount = $message->getResourceAmount();
            $afterAmounts[$rewardGroupId] = $amount;
        }

        // 3回目実行（さらなる変更がないことを確認）
        $result3 = $useCase->exec($currentUser, CarbonImmutable::now());

        $messages3 = app(UsrMessageRepository::class)->getByUserId($userId)
            ->filter(function ($message) {
                $rewardGroupId = $message->getRewardGroupId();
                return $rewardGroupId && str_contains($rewardGroupId, '_2');
            });
            
        $finalAmounts = [];
        foreach ($messages3 as $message) {
            $rewardGroupId = $message->getRewardGroupId();
            $amount = $message->getResourceAmount();
            $finalAmounts[$rewardGroupId] = $amount;
        }

        // 報酬量が変更されていないことを確認
        foreach ($originalAmounts as $groupId => $originalAmount) {
            $this->assertEquals(
                $originalAmount, 
                $afterAmounts[$groupId] ?? 0, 
            );
            $this->assertEquals(
                $originalAmount, 
                $finalAmounts[$groupId] ?? 0, 
            );
        }

        $this->assertEquals(count($messages1), count($messages2));
        $this->assertEquals(count($messages2), count($messages3));
    }

    private function setupPvpTestData(): void
    {
        // PvPシーズンのセットアップ（過去3シーズン + 現在シーズン）
        // シーズン1（古い）
        SysPvpSeason::factory()->create([
            'id' => 1,
            'start_at' => '2024-01-01 00:00:00',
            'end_at' => '2024-01-31 23:59:59',
        ]);

        // シーズン2（ユーザーが参加したシーズン）
        SysPvpSeason::factory()->create([
            'id' => 2,
            'start_at' => '2024-02-01 00:00:00',
            'end_at' => '2024-02-29 23:59:59',
        ]);

        // シーズン3（前シーズン）
        SysPvpSeason::factory()->create([
            'id' => 3,
            'start_at' => '2024-03-01 00:00:00',
            'end_at' => '2024-03-31 23:59:59',
        ]);

        // シーズン4（現在シーズン）
        SysPvpSeason::factory()->create([
            'id' => 4,
            'start_at' => '2024-04-01 00:00:00',
            'end_at' => '2024-04-30 23:59:59',
        ]);

        // PvPランクマスタデータ
        $mstPvpRank = MstPvpRank::factory()->create([
            'rank_class_type' => PvpRankClassType::GOLD->value,
            'rank_class_level' => 2,
        ]);

        // 報酬作成のヘルパー関数
        $createRewardGroupAndRewards = function (SysPvpSeason $season, string $rankClass, int $level, array $amounts, ?PvpRewardCategory $category = null, ?string $condValue = null) use ($mstPvpRank) {
            $category = $category ?? PvpRewardCategory::RANK_ClASS;
            $id = $category->value . '_' . $rankClass . $level . '_' . $season->id;
            $group = MstPvpRewardGroup::factory()->create([
                'id' => $id,
                'mst_pvp_id' => $season->id,
                'reward_category' => $category->value,
                'condition_value' => $condValue ?? $mstPvpRank->id,
            ])->toEntity();
            foreach ($amounts as $amt) {
                MstPvpReward::factory()->create([
                    'mst_pvp_reward_group_id' => $group->getId(),
                    'resource_amount' => $amt,
                ]);
            }
        };

        $season2 = SysPvpSeason::find(2);
        $createRewardGroupAndRewards($season2, 'Gold', 2, [1000], PvpRewardCategory::RANKING, '1-100');
        $createRewardGroupAndRewards($season2, 'Gold', 2, [500]);
    }
}
