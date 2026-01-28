<?php

namespace Feature\Domain\Message\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Enums\Language;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Emblem\Models\UsrEmblem;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Item\Models\UsrItemInterface;
use App\Domain\Message\Constants\MessageConstant;
use App\Domain\Message\Models\Eloquent\UsrMessage;
use App\Domain\Message\Models\LogReceiveMessageReward;
use App\Domain\Message\Repositories\UsrMessageRepository;
use App\Domain\Message\Services\UsrMessageService;
use App\Domain\Message\UseCases\ReceiveUseCase;
use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Pvp\Enums\PvpRewardCategory;
use App\Domain\Pvp\Models\SysPvpSeason;
use App\Domain\Pvp\Models\UsrPvp;
use App\Domain\Pvp\Services\PvpService;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mng\Models\MngMessage;
use App\Domain\Resource\Mng\Models\MngMessageI18n;
use App\Domain\Resource\Mng\Models\MngMessageReward;
use App\Domain\Resource\Mst\Models\MstEmblem;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstPvpRank;
use App\Domain\Resource\Mst\Models\MstPvpReward;
use App\Domain\Resource\Mst\Models\MstPvpRewardGroup;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserLogin;
use App\Domain\User\Models\UsrUserParameter;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Services\CurrencyService;

class ReceiveUseCaseTest extends TestCase
{
    private ReceiveUseCase $useCase;
    private UsrMessageService $usrMessageService;
    private UsrMessageRepository $usrMessageRepository;
    private PvpService $pvpService;


    public function setUp(): void
    {
        parent::setUp();

        $this->usrMessageService = app()->make(UsrMessageService::class);
        $this->usrMessageRepository = app()->make(UsrMessageRepository::class);
        $this->pvpService = app()->make(PvpService::class);
        $this->useCase = app()->make(ReceiveUseCase::class);
    }

    public function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    /**
     * @test
     * @dataProvider execData
     */
    public function exec_報酬受け取りチェック(?string $openedAt, string $exceptedOpenedAt): void
    {
        // Setup
        // ※メッセージ報酬のうち、unit、playerAvatar、playerAvatarFrameは現在のDistributionに実装されてなさそうなので未配布
        CarbonImmutable::setTestNow('2020-01-15 00:00:00');
        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        UsrUserParameter::factory()->create([
            'usr_user_id' => $user->getId(),
            'stamina' => 0,
            'stamina_updated_at' => '2020-01-15 00:00:00',
            'level' => 1,
        ]);
        // 課金基盤情報
        $this->createDiamond($user->getId());
        // レベルアップマスタ作成
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 100],
        ]);
        $mstItemId = 'item_5';
        // アイテムマスタ作成
        MstItem::factory()
            ->set('id', $mstItemId)
            ->create();
        $mstUnitId = 'unit_1';
        MstUnit::factory()
            ->set('id', $mstUnitId)
            ->create();
        $mstEmblemId = 'emblem_1';
        MstEmblem::factory()
            ->set('id', $mstEmblemId)
            ->create();
        // メッセージ報酬1作成
        MngMessage::factory()
            ->set('id', 'message_1')
            ->set('start_at', '2020-01-02 00:00:00')
            ->create();
        MngMessageReward::factory()
            ->set('id', 'distribution_1-1')
            ->set('mng_message_id', 'message_1')
            ->set('resource_type', RewardType::FREE_DIAMOND->value)
            ->set('resource_id', null)
            ->set('resource_amount', 1)
            ->create();
        MngMessageReward::factory()
            ->set('id', 'distribution_1-2')
            ->set('mng_message_id', 'message_1')
            ->set('display_order', 2)
            ->set('resource_type', RewardType::COIN->value)
            ->set('resource_id', null)
            ->set('resource_amount', 2)
            ->create();
        MngMessageReward::factory()
            ->set('id', 'distribution_1-3')
            ->set('mng_message_id', 'message_1')
            ->set('display_order', 3)
            ->set('resource_type', RewardType::EXP->value)
            ->set('resource_id', null)
            ->set('resource_amount', 3)
            ->create();
        MngMessageReward::factory()
            ->set('id', 'distribution_1-4')
            ->set('mng_message_id', 'message_1')
            ->set('display_order', 4)
            ->set('resource_type', RewardType::STAMINA->value)
            ->set('resource_id', null)
            ->set('resource_amount', 4)
            ->create();
        MngMessageI18n::factory()
            ->set('mng_message_id', 'message_1')
            ->set('title', '報酬受け取りテスト1 タイトル')
            ->set('body', '報酬受け取りテスト1 本文')
            ->create();
        UsrMessage::factory()
            ->set('id', 'usr_message_1')
            ->set('usr_user_id', $user->getId())
            ->set('mng_message_id', 'message_1')
            ->set('opened_at', $openedAt)
            ->create();
        // メッセージ報酬2作成
        MngMessage::factory()
            ->set('id', 'message_2')
            ->set('start_at', '2020-01-02 00:00:00')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        MngMessageReward::factory()
            ->set('id', 'distribution_2-1')
            ->set('mng_message_id', 'message_2')
            ->set('display_order', 1)
            ->set('resource_type', RewardType::ITEM->value)
            ->set('resource_id', $mstItemId)
            ->set('resource_amount', 5)
            ->create();
        MngMessageReward::factory()
            ->set('id', 'distribution_2-2')
            ->set('mng_message_id', 'message_2')
            ->set('display_order', 2)
            ->set('resource_type', RewardType::UNIT->value)
            ->set('resource_id', $mstUnitId)
            ->set('resource_amount', 1)
            ->create();
        MngMessageReward::factory()
            ->set('id', 'distribution_2-3')
            ->set('mng_message_id', 'message_2')
            ->set('display_order', 3)
            ->set('resource_type', RewardType::EMBLEM->value)
            ->set('resource_id', $mstEmblemId)
            ->set('resource_amount', 1)
            ->create();
        MngMessageI18n::factory()
            ->set('mng_message_id', 'message_2')
            ->set('title', '報酬受け取りテスト2 タイトル')
            ->set('body', '報酬受け取りテスト2 本文')
            ->create();
        UsrMessage::factory()
            ->set('id', 'usr_message_2')
            ->set('usr_user_id', $user->getId())
            ->set('mng_message_id', 'message_2')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        // 指定対象外報酬作成
        MngMessage::factory()
            ->set('id', 'message_3')
            ->set('start_at', '2020-01-02 00:00:00')
            ->create();
        MngMessageReward::factory()
            ->set('id', 'not_distribution')
            ->set('mng_message_id', 'message_3')
            ->set('resource_type', RewardType::FREE_DIAMOND->value)
            ->set('resource_id', null)
            ->set('resource_amount', 1)
            ->create();
        UsrMessage::factory()
            ->set('id', 'usr_message_3')
            ->set('usr_user_id', $user->getId())
            ->set('mng_message_id', 'message_3')
            ->create();
        // システム送信メッセージ
        UsrMessage::factory()->create([
            'id' => 'usr_message_4',
            'usr_user_id' => $currentUser->getUsrUserId(),
            'mng_message_id' => null,
            'resource_type' => RewardType::COIN->value,
            'resource_id' => null,
            'resource_amount' => 100,
            'title' => 'test_title',
            'body' => 'test_body',
            'expired_at' => '2020-01-24 10:00:00',
            'created_at' => '2020-01-10 10:00:00',
        ]);

        UsrMessage::factory()->createMany([
            [
                'id' => 'group_test_1',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => null,
                'reward_group_id' => 'AAAAAAAA',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 100,
                'title' => 'テスト タイトル GroupTest1',
                'body' => 'テスト 本文 GroupTest1',
            ],
            [
                'id' => 'group_test_2',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => null,
                'reward_group_id' => 'AAAAAAAA',
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_id' => null,
                'resource_amount' => 100,
                'title' => 'テスト タイトル GroupTest1',
                'body' => 'テスト 本文 GroupTest1',
            ],
            [
                'id' => 'group_test_3',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => null,
                'reward_group_id' => 'BBBBBBBB',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 100,
                'title' => 'テスト タイトル GroupTest1',
                'body' => 'テスト 本文 GroupTest1',
            ],
            [
                'id' => 'group_test_4',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => null,
                'reward_group_id' => 'BBBBBBBB',
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_id' => null,
                'resource_amount' => 100,
                'title' => 'テスト タイトル GroupTest1',
                'body' => 'テスト 本文 GroupTest1',
            ],
        ]);
        $messageIds = [
            'usr_message_1',
            'usr_message_2',
            'usr_message_4',
            'group_test_1',
            'group_test_4',
        ];

        // Exercise
        $this->useCase->exec($currentUser, UserConstant::PLATFORM_IOS, $messageIds, Language::Ja->value);

        // Verify
        //  各報酬を受け取っているか
        //   UsrCurrencyFree
        $currency = $this->getDiamond($user->getId());
        $this->assertEquals(201, $currency->getFreeAmount());
        //   UserParameter
        $usrParameter = UsrUserParameter::query()->where('usr_user_id', $user->getId())->first();
        $this->assertEquals(302, $usrParameter->getCoin());
        $this->assertEquals(3, $usrParameter->getExp());
        $this->assertEquals(4, $usrParameter->getStamina());
        //   UserItem
        /** @var UsrItemInterface $userItem */
        $userItem = UsrItem::query()
            ->where('usr_user_id', $user->getId())
            ->where('mst_item_id', $mstItemId)
            ->first();
        $this->assertEquals($mstItemId, $userItem->getMstItemId());
        $this->assertEquals(5, $userItem->getAmount());

        //   UserUnit
        $userUnits = UsrUnit::query()
            ->where('usr_user_id', $user->getId())
            ->get();
        $this->assertEquals(1, $userUnits->count());
        $this->assertEquals($mstUnitId, $userUnits->first()->getMstUnitId());

        //   UserEmblem
        $userEmblems = UsrEmblem::query()
            ->where('usr_user_id', $user->getId())
            ->get();
        $this->assertEquals(1, $userEmblems->count());
        $this->assertEquals($mstEmblemId, $userEmblems->first()->getMstEmblemId());

        //   UsrMessageの受け取り日時が更新されているか
        $usrMessages = UsrMessage::query()
            ->where('usr_user_id', $currentUser->getUsrUserId())
            ->get();
        $message1UsrMessage = $usrMessages->firstWhere('mng_message_id', 'message_1');
        $this->assertEquals($exceptedOpenedAt, $message1UsrMessage->getOpenedAt());
        $this->assertEquals('2020-01-15 00:00:00', $message1UsrMessage->getReceivedAt());
        $this->assertEquals(true, $message1UsrMessage->getIsReceived());

        // ログの確認
        $actual = LogReceiveMessageReward::query()->where('usr_user_id', $user->getId())->get();
        $this->assertCount(1, $actual);
        $this->assertCount(12, json_decode($actual->first()->received_reward, true));
    }

    /**
     * @test
     * @dataProvider execData
     */
    public function exec_グルーピングされたPvP同一ランク帯報酬メッセージの受け取りチェック(?string $openedAt, string $exceptedOpenedAt): void
    {
        // Setup

        // 報酬グループ・報酬データ作成用ヘルパー
        $createRewardGroupAndRewards = function($rankType, $rankLevel, $amounts, $category = PvpRewardCategory::RANK_ClASS->value, $condValue = null) {
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

        // シーズンのランク報酬・ランキング報酬
        $createRewardGroupAndRewards(PvpRankClassType::GOLD->value, 2, [1000, 900]);
        $createRewardGroupAndRewards(PvpRankClassType::GOLD->value, 2, [100, 200], PvpRewardCategory::RANKING->value, '100');
        $createRewardGroupAndRewards(PvpRankClassType::SILVER->value, 1, [500]);
        $createRewardGroupAndRewards(PvpRankClassType::BRONZE->value, 1, [100]);

        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $now = $this->fixTime('2025-09-08 00:00:00');
        UsrUserLogin::factory()->create([
            'usr_user_id' => $usrUserId,
            'first_login_at' => $now->toDateTimeString(),
        ]);

        $currentUser = new CurrentUser($usrUser->getId());
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'stamina' => 0,
            'stamina_updated_at' => '2020-01-15 00:00:00',
            'level' => 1,
        ]);

        // シーズン１のUsrPvp報酬受取り
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

        $gameStartAt = CarbonImmutable::parse($usrUser->getGameStartAt());
        $this->usrMessageService->addNewMessages($usrUserId, $now, Language::Ja->value, $gameStartAt);
        $this->saveAll();

        // シーズン２のUsrPvp報酬受取り
        $now = $now->addMonth();
        $seasons = [];
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

        $lastPlayedSeason2 = $seasons[1];
        $lastPlayedAt = CarbonImmutable::parse($lastPlayedSeason->end_at)->subDay()->toDateTimeString();
        UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $lastPlayedSeason2->getId(),
            'pvp_rank_class_type' => PvpRankClassType::GOLD->value,
            'pvp_rank_class_level' => 2,
            'last_played_at' => $lastPlayedAt,
            'ranking' => 11,
            'is_season_reward_received' => false,
        ]);

        $gameStartAt = CarbonImmutable::parse($usrUser->getGameStartAt());
        $this->usrMessageService->addNewMessages($usrUserId, $now, Language::Ja->value, $gameStartAt);
        $this->saveAll();


        $messages = $this->usrMessageRepository->getByUserId($usrUserId);

        $rankMessageIds = $messages->filter(function ($message) {
            return $message->getTitle() === MessageConstant::PVP_RANK_REWARD_TITLE;
        })->map(fn ($message) => $message->getId());
        $this->assertCount(4, $rankMessageIds);

        $groupIds = $messages->filter(function ($message) {
            return $message->getTitle() === MessageConstant::PVP_RANK_REWARD_TITLE;
        })->map(fn ($message) => $message->getRewardGroupId())->unique()->values();
        $season1messageGroupId = PvpRewardCategory::RANK_ClASS->value . PvpRankClassType::GOLD->value . '2' . '_' . $lastPlayedSeason->id;
        $season2messageGroupId = PvpRewardCategory::RANK_ClASS->value . PvpRankClassType::GOLD->value . '2' . '_' . $lastPlayedSeason2->id;
        $this->assertCount(2, $groupIds);
        $this->assertTrue($groupIds->contains($season1messageGroupId));
        $this->assertTrue($groupIds->contains($season2messageGroupId));

        // 課金基盤情報
        $this->createDiamond($usrUser->getId());

        $messageIds = [
            $rankMessageIds->first(),
        ];

        // Exercise
        $this->useCase->exec($currentUser, UserConstant::PLATFORM_IOS, $messageIds, Language::Ja->value);

        // Verify
        // グルーピングされたメッセージが受け取り済みステータスになっているかチェック
        $usrMessages = UsrMessage::query()->whereIn('id', $rankMessageIds)->get();
        $season1Messages = $usrMessages->where('reward_group_id', $season1messageGroupId);
        $this->assertCount(2, $season1Messages);
        foreach ($season1Messages as $usrMessage) {
            $this->assertEquals(1, $usrMessage->getIsReceived());
        }
        $season2Messages = $usrMessages->where('reward_group_id', $season2messageGroupId);
        $this->assertCount(2, $season2Messages);
        foreach ($season2Messages as $usrMessage) {
            $this->assertEquals(0, $usrMessage->getIsReceived());
        }
    }

    /**
     * @test
     * @dataProvider execData
     */
    public function exec_グルーピングされたメッセージの報酬受け取りチェック(?string $openedAt, string $exceptedOpenedAt): void
    {
        // Setup
        // ※メッセージ報酬のうち、unit、playerAvatar、playerAvatarFrameは現在のDistributionに実装されてなさそうなので未配布
        CarbonImmutable::setTestNow('2020-01-15 00:00:00');
        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        UsrUserParameter::factory()->create([
            'usr_user_id' => $user->getId(),
            'stamina' => 0,
            'stamina_updated_at' => '2020-01-15 00:00:00',
            'level' => 1,
        ]);
        // 課金基盤情報
        $this->createDiamond($user->getId());

        // グルーピングのないメッセージ
        UsrMessage::factory()->createMany([
            [
                'id' => 'usr_message_1',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => null,
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 100,
                'title' => 'test_title',
                'body' => 'test_body',
            ],
            [
                'id' => 'usr_message_2',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => null,
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_id' => null,
                'resource_amount' => 100,
                'title' => 'test_title2',
                'body' => 'test_body2',
            ],
        ]);

        // グルーピングありメッセージ
        UsrMessage::factory()->createMany([
            [
                'id' => 'group_test_1',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => null,
                'reward_group_id' => 'AAAAAAAA',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 100,
                'title' => 'テスト タイトル GroupTest1',
                'body' => 'テスト 本文 GroupTest1',
            ],
            [
                'id' => 'group_test_2',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => null,
                'reward_group_id' => 'AAAAAAAA',
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_id' => null,
                'resource_amount' => 100,
                'title' => 'テスト タイトル GroupTest1',
                'body' => 'テスト 本文 GroupTest1',
            ],
        ]);
        $messageIds = [
            'usr_message_1',
            'usr_message_2',
            'group_test_1',
        ];

        // Exercise
        $this->useCase->exec($currentUser, UserConstant::PLATFORM_IOS, $messageIds, Language::Ja->value);

        // Verify
        // 対象となっていないgroup_test_２がグルーピングされて付与されているか確認
        $currency = $this->getDiamond($user->getId());
        $this->assertEquals(200, $currency->getFreeAmount());

        $usrParameter = UsrUserParameter::query()->where('usr_user_id', $user->getId())->first();
        $this->assertEquals(200, $usrParameter->getCoin());

        // グルーピングされたメッセージも受け取り済みステータスになっているかチェック
        $usrMessages = UsrMessage::query()->whereIn('id', ['group_test_1', 'group_test_2'])->get();
        foreach ($usrMessages as $usrMessage) {
            $this->assertEquals(1, $usrMessage->getIsReceived());
        }
    }

    /**
     * @return array
     */
    public static function execData(): array
    {
        return [
            '既読日時がnull' => [null, '2020-01-15 00:00:00'],
            '既読日時が登録済み' => ['2020-01-14 23:45:00', '2020-01-14 23:45:00'],
        ];
    }

    public function test_exec_報酬受け取り時システムメッセージだけだった場合のテスト(): void
    {
        // Setup
        $this->fixTime('2020-01-15 00:00:00');
        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        UsrUserParameter::factory()->create([
            'usr_user_id' => $user->getId(),
            'stamina' => 0,
            'stamina_updated_at' => '2020-01-15 00:00:00',
            'level' => 1,
        ]);
        // 課金基盤情報
        $this->createDiamond($user->getId());
        // システム送信メッセージ
        UsrMessage::factory()->create([
            'id' => 'usr_message_1',
            'usr_user_id' => $currentUser->getUsrUserId(),
            'mng_message_id' => null,
            'resource_type' => RewardType::COIN->value,
            'resource_id' => null,
            'resource_amount' => 100,
            'title' => 'test_title',
            'body' => 'test_body',
            'expired_at' => '2020-01-24 10:00:00',
            'created_at' => '2020-01-10 10:00:00',
        ]);
        UsrMessage::factory()->create([
            'id' => 'usr_message_2',
            'usr_user_id' => $currentUser->getUsrUserId(),
            'mng_message_id' => null,
            'resource_type' => RewardType::COIN->value,
            'resource_id' => null,
            'resource_amount' => 10,
            'title' => 'test_title',
            'body' => 'test_body',
            'expired_at' => '2020-01-24 10:00:00',
            'created_at' => '2020-01-10 10:00:00',
        ]);
        UsrMessage::factory()->create([
            'id' => 'usr_message_3',
            'usr_user_id' => $currentUser->getUsrUserId(),
            'mng_message_id' => null,
            'resource_type' => RewardType::COIN->value,
            'resource_id' => null,
            'resource_amount' => 1,
            'title' => 'test_title',
            'body' => 'test_body',
            'expired_at' => '2020-01-24 10:00:00',
            'created_at' => '2020-01-10 10:00:00',
        ]);

        $messageIds = [
            'usr_message_1',
            'usr_message_2',
            'usr_message_3'
        ];

        // Exercise
        $this->useCase->exec($currentUser, UserConstant::PLATFORM_IOS, $messageIds, Language::Ja->value);

        // Verify
        //  報酬の受け取り確認
        $usrParameter = UsrUserParameter::query()->where('usr_user_id', $user->getId())->first();
        $this->assertEquals(111, $usrParameter->getCoin());
    }

    /**
     * @test
     */
    public function exec_例外_ユーザーに送信されてないmessageIdだった(): void
    {
        // Setup
        CarbonImmutable::setTestNow('2020-01-15 00:00:00');
        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        UsrUserParameter::factory()->create([
            'usr_user_id' => $user->getId(),
            'stamina' => 0,
            'stamina_updated_at' => '2020-01-15 00:00:00',
            'level' => 1,
        ]);
        // 課金基盤情報
        $this->createDiamond($user->getId());
        // レベルアップマスタ作成
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 100],
        ]);
        MngMessage::factory()
            ->set('id', 'message_1')
            ->set('start_at', '2020-01-02 00:00:00')
            ->create();
        MngMessageReward::factory()
            ->set('id', 'distribution_1-1')
            ->set('mng_message_id', 'message_1')
            ->set('resource_type', RewardType::FREE_DIAMOND->value)
            ->set('resource_id', '1')
            ->set('resource_amount', 1)
            ->create();
        MngMessageI18n::factory()
            ->set('mng_message_id', 'message_1')
            ->set('title', '報酬受け取りテスト1 タイトル')
            ->set('body', '報酬受け取りテスト1 本文')
            ->create();
        UsrMessage::factory()
            ->set('id', 'usr_message_1')
            ->set('usr_user_id', $user->getId())
            ->set('mng_message_id', 'message_1')
            ->create();
        $messageIds = [
            'usr_message_1',
            'usr_message_2',// 未登録のメッセージID
        ];

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::INVALID_PARAMETER);
        //$this->expectExceptionMessage(
        //    'not found usr_message_id'
        //);
        $this->useCase->exec($currentUser, UserConstant::PLATFORM_IOS, $messageIds, Language::Ja->value);
    }

    /**
     * @test
     */
    public function exec_例外_期限切れの報酬だった(): void
    {
        // Setup
        CarbonImmutable::setTestNow('2020-01-15 00:00:00');
        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        UsrUserParameter::factory()->create([
            'usr_user_id' => $user->getId(),
            'stamina' => 0,
            'stamina_updated_at' => '2020-01-15 00:00:00',
            'level' => 1,
        ]);
        // 課金基盤情報
        $this->createDiamond($user->getId());
        // レベルアップマスタ作成
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 100],
        ]);
        MngMessage::factory()
            ->set('id', 'message_1')
            ->set('start_at', '2020-01-02 00:00:00')
            ->create();
        MngMessageReward::factory()
            ->set('id', 'distribution_1-1')
            ->set('mng_message_id', 'message_1')
            ->set('resource_type', RewardType::FREE_DIAMOND->value)
            ->set('resource_id', '1')
            ->set('resource_amount', 1)
            ->create();
        MngMessageI18n::factory()
            ->set('mng_message_id', 'message_1')
            ->set('title', '報酬受け取りテスト1 タイトル')
            ->set('body', '報酬受け取りテスト1 本文')
            ->create();
        UsrMessage::factory()
            ->set('id', 'usr_message_1')
            ->set('usr_user_id', $user->getId())
            ->set('mng_message_id', 'message_1')
            ->set('expired_at', '2020-01-14 23:59:59')
            ->create();
        $messageIds = [
            'usr_message_1',
        ];

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::EXPIRED_MESSAGE_RESOURCE);
        $this->expectExceptionMessage(
            'Expired MessageDistribution mngMessageId:message_1'
        );
        $this->useCase->exec($currentUser, UserConstant::PLATFORM_IOS, $messageIds, Language::Ja->value);
    }

    public function test_exec_例外_既に報酬を受け取り済みのメッセージから報酬を受け取ろうとした(): void
    {
        // Setup
        $this->fixTime('2020-01-15 00:00:00');
        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        UsrUserParameter::factory()->create([
            'usr_user_id' => $user->getId(),
            'stamina' => 0,
            'stamina_updated_at' => '2020-01-15 00:00:00',
            'level' => 1,
        ]);
        // 課金基盤情報
        $this->createDiamond($user->getId());
        // システム送信メッセージ
        UsrMessage::factory()->create([
            'id' => 'usr_message_1',
            'usr_user_id' => $currentUser->getUsrUserId(),
            'mng_message_id' => null,
            'resource_type' => RewardType::COIN->value,
            'resource_id' => null,
            'resource_amount' => 100,
            'title' => 'test_title',
            'body' => 'test_body',
            'expired_at' => '2020-01-24 10:00:00',
            'created_at' => '2020-01-10 10:00:00',
        ]);
        UsrMessage::factory()->create([
            'id' => 'usr_message_2',
            'usr_user_id' => $currentUser->getUsrUserId(),
            'mng_message_id' => null,
            'resource_type' => RewardType::COIN->value,
            'resource_id' => null,
            'resource_amount' => 10,
            'title' => 'test_title',
            'body' => 'test_body',
            'expired_at' => '2020-01-24 10:00:00',
            'created_at' => '2020-01-10 10:00:00',
        ]);
        UsrMessage::factory()->create([
            'id' => 'usr_message_3',
            'usr_user_id' => $currentUser->getUsrUserId(),
            'mng_message_id' => null,
            'resource_type' => RewardType::COIN->value,
            'resource_id' => null,
            'resource_amount' => 1,
            'title' => 'test_title',
            'body' => 'test_body',
            'expired_at' => '2020-01-24 10:00:00',
            'created_at' => '2020-01-10 10:00:00',
        ]);

        $messageIds = [
            'usr_message_1',
            'usr_message_2',
            'usr_message_3'
        ];

        // Exercise
        $this->useCase->exec($currentUser, UserConstant::PLATFORM_IOS, $messageIds, Language::Ja->value);

        // Verify
        //  報酬の受け取り確認
        $usrParameter = UsrUserParameter::query()->where('usr_user_id', $user->getId())->first();
        $this->assertEquals(111, $usrParameter->getCoin());
        //  UsrMessageの受け取り日時が更新されているか
        $usrMessages = UsrMessage::query()
            ->where('usr_user_id', $currentUser->getUsrUserId())
            ->get();
        $message1UsrMessage = $usrMessages->firstWhere('id', 'usr_message_1');
        $this->assertEquals('2020-01-15 00:00:00', $message1UsrMessage->getReceivedAt());
        $this->assertEquals(true, $message1UsrMessage->getIsReceived());

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::INVALID_PARAMETER);
        $this->useCase->exec($currentUser, UserConstant::PLATFORM_IOS, $messageIds, Language::Ja->value);
    }

    public static function params_test_exec_リワードの受け取り上限超過(): array
    {
        return [
            '無償ダイヤモンド超過' => [
                'freeDiamond' => 999999999,
                'itemCount' => 0,
                'stamina' => 0,
                'coin' => 0,
            ],
            'アイテム所持数超過' => [
                'freeDiamond' => 0,
                'itemCount' => 999999999,
                'stamina' => 0,
                'coin' => 0,
            ],
            'スタミナ超過' => [
                'freeDiamond' => 0,
                'itemCount' => 0,
                'stamina' => 999,
                'coin' => 0,
            ],
            'コイン超過' => [
                'freeDiamond' => 0,
                'itemCount' => 0,
                'stamina' => 0,
                'coin' => 99999999,
            ],
        ];
    }

    #[DataProvider('params_test_exec_リワードの受け取り上限超過')]
    public function test_exec_リワードの受け取り上限超過したらエラーになって受け取りできない(
        int $freeDiamond,
        int $itemCount,
        int $stamina,
        int $coin
    ): void
    {
        // Setup
        $this->fixTime('2025-06-01 00:00:00');
        $now = $this->fixTime();
        $user = $this->createUsrUser();
        $usrUserId = $user->getId();
        $currentUser = new CurrentUser($usrUserId);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $user->getId(),
            'stamina' => $stamina,
            'coin' => $coin,
        ]);

        $this->createDiamond(
            usrUserId: $usrUserId,
            freeDiamond: $freeDiamond,
        );
        $mstItemId = 'mdt_item_id_1';
        $userItemAmount = $itemCount;
        // アイテムを所持している状態にする
        MstItem::factory()
            ->set('id', $mstItemId)
            ->create();

        UsrItem::factory()->create([
            'usr_user_id' => $currentUser->getId(),
            'mst_item_id' => $mstItemId,
            'amount'      => $userItemAmount,
        ]);

        MngMessage::factory()
            ->set('id', 'message_1')
            ->set('start_at', $now)
            ->create();
        MngMessageReward::factory()
            ->set('id', 'distribution_1-1')
            ->set('mng_message_id', 'message_1')
            ->set('display_order',1)
            ->set('resource_type', RewardType::FREE_DIAMOND->value)
            ->set('resource_id', null)
            ->set('resource_amount', 100)
            ->create();
         MngMessageReward::factory()
            ->set('id', 'distribution_1-2')
            ->set('mng_message_id', 'message_1')
            ->set('display_order', 2)
            ->set('resource_type', RewardType::STAMINA->value)
            ->set('resource_id', null)
            ->set('resource_amount', 10)
            ->create();
        MngMessageReward::factory()
            ->set('id', 'distribution_1-3')
            ->set('mng_message_id', 'message_1')
            ->set('display_order', 3)
            ->set('resource_type', RewardType::ITEM->value)
            ->set('resource_id', 'mdt_item_id_1')
            ->set('resource_amount', 5)
            ->create();
        MngMessageI18n::factory()
            ->set('mng_message_id', 'message_1')
            ->set('title', '報酬受け取りテスト1 タイトル')
            ->set('body', '報酬受け取りテスト1 本文')
            ->create();
        UsrMessage::factory()
            ->set('id', 'usr_message_1')
            ->set('usr_user_id', $user->getId())
            ->set('mng_message_id', 'message_1')
            ->set('opened_at', $now)
            ->create();

        // グルーピングのないメッセージ
        UsrMessage::factory()->createMany([
            [
                'id' => 'sys_message_1',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => null,
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 100,
                'title' => 'test_title',
                'body' => 'test_body',
            ],
            [
                'id' => 'sys_message_2',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => null,
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_id' => null,
                'resource_amount' => 100,
                'title' => 'test_title2',
                'body' => 'test_body2',
            ],
        ]);

        // グルーピングありメッセージ
        UsrMessage::factory()->createMany([
            [
                'id' => 'group_test_1',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => null,
                'reward_group_id' => 'AAAAAAAA',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 100,
                'title' => 'テスト タイトル GroupTest1',
                'body' => 'テスト 本文 GroupTest1',
            ],
            [
                'id' => 'group_test_2',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => null,
                'reward_group_id' => 'AAAAAAAA',
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_id' => null,
                'resource_amount' => 100,
                'title' => 'テスト タイトル GroupTest1',
                'body' => 'テスト 本文 GroupTest1',
            ],
        ]);

        $messageIds = [
            'usr_message_1',
            'sys_message_1',
            'sys_message_2',
            'group_test_1',
        ];

        // エラー
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::MESSAGE_REWARD_BY_OVER_MAX);

        // Exercise
        $this->useCase->exec($currentUser, UserConstant::PLATFORM_IOS, $messageIds, Language::Ja->value);

        // Verify
    }

    public static function params_リワードの受け取りで上限値ピッタリ(): array
    {
        // testで付与されるリワード
        // 無償ダイヤモンド: 300
        // アイテム: 5
        // スタミナ: 10
        // コイン: 200
        return [
            '上限値と一致させる' => [
                'freeDiamond' => 999999699,
                'itemCount' => 999999994,
                'stamina' => 989,
                'coin' => 99999799,
            ],
        ];
    }

    #[DataProvider('params_リワードの受け取りで上限値ピッタリ')]
    public function test_exec_リワードの受け取りで上限値ピッタリでエラーなし(
        int $freeDiamond,
        int $itemCount,
        int $stamina,
        int $coin
    ): void
    {
        // Setup
        $this->fixTime('2025-06-01 00:00:00');
        $now = $this->fixTime();
        $user = $this->createUsrUser();
        $usrUserId = $user->getId();
        $currentUser = new CurrentUser($usrUserId);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $user->getId(),
            'stamina' => $stamina,
            'coin' => $coin,
        ]);

        $this->createDiamond(
            usrUserId: $usrUserId,
            freeDiamond: $freeDiamond,
        );
        $mstItemId = 'mdt_item_id_1';
        $userItemAmount = $itemCount;
        // アイテムを所持している状態にする
        MstItem::factory()
            ->set('id', $mstItemId)
            ->create();

        UsrItem::factory()->create([
            'usr_user_id' => $currentUser->getId(),
            'mst_item_id' => $mstItemId,
            'amount'      => $userItemAmount,
        ]);

        MngMessage::factory()
            ->set('id', 'message_1')
            ->set('start_at', $now)
            ->create();
        MngMessageReward::factory()
            ->set('id', 'distribution_1-1')
            ->set('mng_message_id', 'message_1')
            ->set('display_order',1)
            ->set('resource_type', RewardType::FREE_DIAMOND->value)
            ->set('resource_id', null)
            ->set('resource_amount', 100)
            ->create();
         MngMessageReward::factory()
            ->set('id', 'distribution_1-2')
            ->set('mng_message_id', 'message_1')
            ->set('display_order', 2)
            ->set('resource_type', RewardType::STAMINA->value)
            ->set('resource_id', null)
            ->set('resource_amount', 10)
            ->create();
        MngMessageReward::factory()
            ->set('id', 'distribution_1-3')
            ->set('mng_message_id', 'message_1')
            ->set('display_order', 3)
            ->set('resource_type', RewardType::ITEM->value)
            ->set('resource_id', 'mdt_item_id_1')
            ->set('resource_amount', 5)
            ->create();
        MngMessageI18n::factory()
            ->set('mng_message_id', 'message_1')
            ->set('title', '報酬受け取りテスト1 タイトル')
            ->set('body', '報酬受け取りテスト1 本文')
            ->create();
        UsrMessage::factory()
            ->set('id', 'usr_message_1')
            ->set('usr_user_id', $user->getId())
            ->set('mng_message_id', 'message_1')
            ->set('opened_at', $now)
            ->create();

        // グルーピングのないメッセージ
        UsrMessage::factory()->createMany([
            [
                'id' => 'sys_message_1',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => null,
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 100,
                'title' => 'test_title',
                'body' => 'test_body',
            ],
            [
                'id' => 'sys_message_2',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => null,
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_id' => null,
                'resource_amount' => 100,
                'title' => 'test_title2',
                'body' => 'test_body2',
            ],
        ]);

        // グルーピングありメッセージ
        UsrMessage::factory()->createMany([
            [
                'id' => 'group_test_1',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => null,
                'reward_group_id' => 'AAAAAAAA',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 100,
                'title' => 'テスト タイトル GroupTest1',
                'body' => 'テスト 本文 GroupTest1',
            ],
            [
                'id' => 'group_test_2',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => null,
                'reward_group_id' => 'AAAAAAAA',
                'resource_type' => RewardType::FREE_DIAMOND->value,
                'resource_id' => null,
                'resource_amount' => 100,
                'title' => 'テスト タイトル GroupTest1',
                'body' => 'テスト 本文 GroupTest1',
            ],
        ]);

        $messageIds = [
            'usr_message_1',
            'sys_message_1',
            'sys_message_2',
            'group_test_1',
        ];

        // Exercise
        $this->useCase->exec($currentUser, UserConstant::PLATFORM_IOS, $messageIds, Language::Ja->value);

        // Verify
        // Userparameterの確認
        $usrParameter = UsrUserParameter::query()->where('usr_user_id', $currentUser->getUsrUserId())->first();
        $this->assertEquals(999, $usrParameter->getStamina());
        $this->assertEquals(99999999, $usrParameter->getCoin());
        //　無償ダイヤモンドの確認
        $currency = $this->getDiamond($currentUser->getUsrUserId());
        $this->assertEquals(999999999, $currency->getFreeAmount());
        // アイテムの確認
        $userItem = UsrItem::query()
            ->where('usr_user_id', $currentUser->getUsrUserId())
            ->where('mst_item_id', $mstItemId)
            ->first();
        $this->assertEquals(999999999, $userItem->getAmount());
    }
}
