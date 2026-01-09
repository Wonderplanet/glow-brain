<?php

namespace Tests\Feature\Http\Controllers;

use App\Domain\Common\Constants\System;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Message\Enums\MngMessageType;
use App\Domain\Message\Models\Eloquent\UsrMessage;
use App\Domain\Message\Repositories\UsrMessageRepository;
use App\Domain\Message\UseCases\OpenUseCase;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Enums\UnreceivedRewardReason;
use App\Domain\Resource\Mng\Models\MngMessage;
use App\Domain\Resource\Mng\Models\MngMessageI18n;
use App\Domain\Resource\Mng\Models\MngMessageReward;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Resource\Mst\Models\MstUserLevelBonus;
use App\Domain\Resource\Mst\Models\MstUserLevelBonusGroup;
use App\Domain\User\Models\UsrUserLogin;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserParameterInterface;
use App\Exceptions\HttpStatusCode;
use Carbon\CarbonImmutable;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class MessageControllerTest extends BaseControllerTestCase
{
    protected string $baseUrl = '/api/message/';

    private MngMessage $mngMessage;
    private MngMessageReward $mngMessageReward;
    private MngMessageI18n $mngMessageI18n;
    private UsrMessageRepository $usrMessageRepository;
    private UsrMessage $usrMessage;

    public function setUp(): void
    {
        parent::setUp();
        $this->mngMessage = $this->app->make(MngMessage::class);
        $this->mngMessageReward = $this->app->make(MngMessageReward::class);
        $this->mngMessageI18n = $this->app->make(MngMessageI18n::class);
        $this->usrMessageRepository = $this->app->make(UsrMessageRepository::class);
        $this->usrMessage = $this->app->make(UsrMessage::class);
    }

    public function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function updateAndFetch_一覧取得確認(): void
    {
        CarbonImmutable::setTestNow('2020-01-15 00:00:00');
        $usrUser = $this->createUsrUser();
        $userId = $usrUser->getId();
        UsrUserLogin::factory()->create([
            'usr_user_id' => $userId,
            'first_login_at' => '2020-01-01 00:00:00',
        ]);

        // Setup
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
            ->set('id', 'usr_message_out1')
            ->set('usr_user_id', $userId)
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
            ->set('id', 'usr_message_out_2')
            ->set('usr_user_id', fake()->uuid)
            ->set('mng_message_id', 'out2')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        // メッセージのみ 既読済み
        MngMessage::factory()
            ->set('id', 'message_1')
            ->set('start_at', '2020-01-01 00:01:00')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        MngMessageI18n::factory()
            ->set('mng_message_id', 'message_1')
            ->set('title', 'テスト タイトル 既読')
            ->set('body', 'テスト 本文 既読')
            ->create();
        UsrMessage::factory()
            ->set('id', 'usr_message_1')
            ->set('usr_user_id', $userId)
            ->set('mng_message_id', 'message_1')
            ->set('opened_at', '2020-01-01 00:00:00')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        // 配布物あり(1種) 受け取り済み
        MngMessage::factory()
            ->set('id', 'message_2')
            ->set('start_at', '2020-01-01 12:00:00')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        MngMessageReward::factory()
            ->set('id', 'distribution_1')
            ->set('mng_message_id', 'message_2')
            ->set('resource_type', RewardType::FREE_DIAMOND->value)
            ->set('resource_id', '1')
            ->set('resource_amount', 1)
            ->create();
        MngMessageI18n::factory()
            ->set('mng_message_id', 'message_2')
            ->set('title', 'テスト タイトル 受け取り済み')
            ->set('body', 'テスト 本文 受け取り済み')
            ->create();
        UsrMessage::factory()
            ->set('id', 'usr_message_2')
            ->set('usr_user_id', $userId)
            ->set('mng_message_id', 'message_2')
            ->set('opened_at', '2020-01-03 11:10:00')
            ->set('received_at', '2020-01-03 11:10:00')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        // メッセージのみ 未読
        MngMessage::factory()
            ->set('id', 'message_3')
            ->set('start_at', '2020-01-02 00:00:00')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        MngMessageI18n::factory()
            ->set('mng_message_id', 'message_3')
            ->set('title', 'テスト タイトル メッセージ未読')
            ->set('body', 'テスト 本文 メッセージ未読')
            ->create();
        UsrMessage::factory()
            ->set('id', 'usr_message_3')
            ->set('usr_user_id', $userId)
            ->set('mng_message_id', 'message_3')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        // 配布物あり(2種) 未受け取り
        MngMessage::factory()
            ->set('id', 'message_4')
            ->set('start_at', '2020-01-02 00:00:00')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        MngMessageReward::factory()
            ->set('id', 'distribution_3')
            ->set('mng_message_id', 'message_4')
            ->set('display_order', 2)
            ->set('resource_type', RewardType::STAMINA->value)
            ->set('resource_id', '2')
            ->set('resource_amount', 100)
            ->create();
        MngMessageReward::factory()
            ->set('id', 'distribution_2')
            ->set('mng_message_id', 'message_4')
            ->set('resource_type', RewardType::FREE_DIAMOND->value)
            ->set('resource_id', '1')
            ->set('resource_amount', 1)
            ->create();
        MngMessageI18n::factory()
            ->set('mng_message_id', 'message_4')
            ->set('title', 'テスト タイトル 未受け取り')
            ->set('body', 'テスト 本文 未受け取り')
            ->create();
        UsrMessage::factory()
            ->set('id', 'usr_message_4')
            ->set('usr_user_id', $userId)
            ->set('mng_message_id', 'message_4')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();

        // reward_group_idのグルーピングテスト用
        UsrMessage::factory()
            ->set('id', 'group_test_1')
            ->set('usr_user_id', $userId)
            ->set('mng_message_id', null)
            ->set('title', 'テスト タイトル GroupTest1')
            ->set('body', 'テスト 本文 GroupTest1')
            ->set('reward_group_id', 'AAAAAAAA')
            ->set('resource_type', RewardType::COIN->value)
            ->set('resource_id', null)
            ->set('resource_amount', 100)
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        UsrMessage::factory()
            ->set('id', 'group_test_2')
            ->set('usr_user_id', $userId)
            ->set('mng_message_id', null)
            ->set('title', 'テスト タイトル GroupTest1')
            ->set('body', 'テスト 本文 GroupTest1')
            ->set('reward_group_id', 'AAAAAAAA')
            ->set('resource_type', RewardType::FREE_DIAMOND->value)
            ->set('resource_id', null)
            ->set('resource_amount', 100)
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();

        // Exercise
        $response = $this
            ->withHeader(System::HEADER_LANGUAGE, 'ja')
            ->sendRequest('update_and_fetch');

        // Verify
        //  ステータスチェック
        $response->assertStatus(HttpStatusCode::SUCCESS);
        //  件数チェック(実際は６入っているがグルーピングがあるため５)
        $this->assertCount(5, $response['messages']);
        //  配信開始日時(startAt)の新しい順 > messageIdの昇順でソートされていて、値が想定通りかチェック
        $this->assertEquals(
            [
                'messages' => [
                    [
                        'usrMessageId' => 'group_test_1',
                        'oprMessageId' => null,
                        'startAt' => '2020-01-15T00:00:00+00:00',
                        'openedAt' => null,
                        'receivedAt' => null,
                        'expiredAt' => '2020-01-31T23:59:59+00:00',
                        'messageRewards' => [
                            [
                                'reward' => [
                                    'resourceType' => RewardType::COIN->value,
                                    'resourceId' => null,
                                    'resourceAmount' => 100,
                                    'unreceivedRewardReasonType' => UnreceivedRewardReason::NONE->value,
                                    'preConversionResource' => null,
                                ],
                            ],
                            [
                                'reward' => [
                                    'resourceType' => RewardType::FREE_DIAMOND->value,
                                    'resourceId' => null,
                                    'resourceAmount' => 100,
                                    'unreceivedRewardReasonType' => UnreceivedRewardReason::NONE->value,
                                    'preConversionResource' => null,
                                ],
                            ],
                        ],
                        'title' => 'テスト タイトル GroupTest1',
                        'body' => 'テスト 本文 GroupTest1',
                    ],
                    [
                        'usrMessageId' => 'usr_message_3',
                        'oprMessageId' => 'message_3',
                        'startAt' => '2020-01-02T00:00:00+00:00',
                        'openedAt' => null,
                        'receivedAt' => null,
                        'expiredAt' => '2020-01-31T23:59:59+00:00',
                        'messageRewards' => [],
                        'title' => 'テスト タイトル メッセージ未読',
                        'body' => 'テスト 本文 メッセージ未読',
                    ],
                    [
                        'usrMessageId' => 'usr_message_4',
                        'oprMessageId' => 'message_4',
                        'startAt' => '2020-01-02T00:00:00+00:00',
                        'openedAt' => null,
                        'receivedAt' => null,
                        'expiredAt' => '2020-01-31T23:59:59+00:00',
                        'messageRewards' => [
                            [
                                'reward' => [
                                    'resourceType' => RewardType::FREE_DIAMOND->value,
                                    'resourceId' => '1',
                                    'resourceAmount' => 1,
                                    'unreceivedRewardReasonType' => UnreceivedRewardReason::NONE->value,
                                    'preConversionResource' => null,
                                ],
                            ],
                            [
                                'reward' => [
                                    'resourceType' => RewardType::STAMINA->value,
                                    'resourceId' => '2',
                                    'resourceAmount' => 100,
                                    'unreceivedRewardReasonType' => UnreceivedRewardReason::NONE->value,
                                    'preConversionResource' => null,
                                ],
                            ],
                        ],
                        'title' => 'テスト タイトル 未受け取り',
                        'body' => 'テスト 本文 未受け取り',
                    ],
                    [
                        'usrMessageId' => 'usr_message_2',
                        'oprMessageId' => 'message_2',
                        'startAt' => '2020-01-01T12:00:00+00:00',
                        'openedAt' => '2020-01-03T11:10:00+00:00',
                        'receivedAt' => '2020-01-03T11:10:00+00:00',
                        'expiredAt' => '2020-01-31T23:59:59+00:00',
                        'messageRewards' => [
                            [
                                'reward' => [
                                    'resourceType' => RewardType::FREE_DIAMOND->value,
                                    'resourceId' => '1',
                                    'resourceAmount' => 1,
                                    'unreceivedRewardReasonType' => UnreceivedRewardReason::NONE->value,
                                    'preConversionResource' => null,
                                ],
                            ],
                        ],
                        'title' => 'テスト タイトル 受け取り済み',
                        'body' => 'テスト 本文 受け取り済み',
                    ],
                    [
                        'usrMessageId' => 'usr_message_1',
                        'oprMessageId' => 'message_1',
                        'startAt' => '2020-01-01T00:01:00+00:00',
                        'openedAt' => '2020-01-01T00:00:00+00:00',
                        'receivedAt' => null,
                        'expiredAt' => '2020-01-31T23:59:59+00:00',
                        'messageRewards' => [],
                        'title' => 'テスト タイトル 既読',
                        'body' => 'テスト 本文 既読',
                    ],
                ]
            ],
            $response->json()
        );
    }

    /**
     * @test
     */
    public function updateAndFetch_一覧が空(): void
    {
        CarbonImmutable::setTestNow('2020-01-15 00:00:00');
        $usrUser = $this->createUsrUser();
        $userId = $usrUser->getId();
        UsrUserLogin::factory()->create([
            'usr_user_id' => $userId,
            'first_login_at' => '2020-01-01 00:00:00',
        ]);
        // Exercise
        $response = $this
            ->withHeader(System::HEADER_LANGUAGE, 'ja')
            ->sendRequest('update_and_fetch');

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
        //  中身が空であること
        $this->assertEmpty($response['messages']);
    }

    /**
     * @test
     */
    public function opened_クエストを送ると200OKが返り想定通りのレスポンスが返ることを確認する(): void
    {
        // Setup
        $this->mock(OpenUseCase::class, function (MockInterface $mock) {
            $mock->shouldReceive('exec');
        });
        $param = [
            'usrMessageIds' => [
                'dcb649ef-4e66-14f6-33bd-29da50f29361',
                'dcb649ef-4e66-14f6-33bd-29da50f29362',
                'dcb649ef-4e66-14f6-33bd-29da50f29363',
            ],
        ];

        // Exercise
        $response = $this->sendRequest('open', $param);

        // Verify
        //  ステータスチェック
        $response->assertStatus(HttpStatusCode::SUCCESS);
    }

    /**
     * @test
     */
    public function received_リクエストを送ると200OKが返り想定通りのレスポンスが返ることを確認する(): void
    {
        CarbonImmutable::setTestNow('2020-01-15 00:00:00');
        $usrUser = $this->createUsrUser();
        $userId = $usrUser->getId();
        $this->createDiamond($userId);
        /** @var UsrUserParameterInterface $usrUserParameter */
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $userId,
            'stamina_updated_at' => now()->sub('1 hour'),
        ]);

        // Setup
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
            ->set('usr_user_id', $userId)
            ->set('mng_message_id', 'out1')
            ->set('expired_at', '2019-12-31 23:59:59')
            ->create();
        //  別ユーザー
        MngMessage::factory()
            ->set('id', 'out2')
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
        MngMessage::factory()
            ->set('id', 'message_1')
            ->set('start_at', '2020-01-01 00:01:00')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        MngMessageI18n::factory()
            ->set('mng_message_id', 'message_1')
            ->set('title', 'テスト タイトル 既読')
            ->set('body', 'テスト 本文 既読')
            ->create();
        UsrMessage::factory()
            ->set('usr_user_id', $userId)
            ->set('mng_message_id', 'message_1')
            ->set('opened_at', '2020-01-01 00:00:00')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        // 配布物あり(1種) 受け取り済み
        MngMessage::factory()
            ->set('id', 'message_2')
            ->set('start_at', '2020-01-01 12:00:00')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        MngMessageReward::factory()
            ->set('id', 'distribution_1')
            ->set('mng_message_id', 'message_2')
            ->set('resource_type', RewardType::FREE_DIAMOND->value)
            ->set('resource_id', '1')
            ->set('resource_amount', 1)
            ->create();
        MngMessageI18n::factory()
            ->set('mng_message_id', 'message_2')
            ->set('title', 'テスト タイトル 受け取り済み')
            ->set('body', 'テスト 本文 受け取り済み')
            ->create();
        UsrMessage::factory()
            ->set('usr_user_id', $userId)
            ->set('mng_message_id', 'message_2')
            ->set('opened_at', '2020-01-03 11:10:00')
            ->set('received_at', '2020-01-03 11:10:00')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        // メッセージのみ 未読
        MngMessage::factory()
            ->set('id', 'message_3')
            ->set('start_at', '2020-01-02 00:00:00')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        MngMessageI18n::factory()
            ->set('mng_message_id', 'message_3')
            ->set('title', 'テスト タイトル メッセージ未読')
            ->set('body', 'テスト 本文 メッセージ未読')
            ->create();
        UsrMessage::factory()
            ->set('usr_user_id', $userId)
            ->set('mng_message_id', 'message_3')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        // 配布物あり(2種) 未受け取り
        MngMessage::factory()
            ->set('id', 'message_4')
            ->set('start_at', '2020-01-02 00:00:00')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        MngMessageReward::factory()
            ->set('id', 'distribution_3')
            ->set('mng_message_id', 'message_4')
            ->set('display_order', 2)
            ->set('resource_type', RewardType::ITEM->value)
            ->set('resource_id', 'item1')
            ->set('resource_amount', 100)
            ->create();
        MngMessageReward::factory()
            ->set('id', 'distribution_4')
            ->set('mng_message_id', 'message_4')
            ->set('display_order', 3)
            ->set('resource_type', RewardType::EXP->value)
            ->set('resource_id', null)
            ->set('resource_amount', 100)
            ->create();
        MngMessageReward::factory()
            ->set('id', 'distribution_2')
            ->set('mng_message_id', 'message_4')
            ->set('resource_type', RewardType::FREE_DIAMOND->value)
            ->set('resource_id', '1')
            ->set('resource_amount', 1)
            ->create();
        MngMessageI18n::factory()
            ->set('mng_message_id', 'message_4')
            ->set('title', 'テスト タイトル 未受け取り')
            ->set('body', 'テスト 本文 未受け取り')
            ->create();
        UsrMessage::factory()
            ->set('id', 'dcb649ef-4e66-14f6-33bd-29da50f2936f')
            ->set('usr_user_id', $userId)
            ->set('mng_message_id', 'message_4')
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 10],
            ['level' => 3, 'stamina' => 10, 'exp' => 100],
        ]);
        MstUserLevelBonus::factory()->create([
            'level' => 3,
            'mst_user_level_bonus_group_id' => 'usrLevelBonusGroup1',
        ]);
        MstUserLevelBonusGroup::factory()->createMany([
            [
                'mst_user_level_bonus_group_id' => 'usrLevelBonusGroup1',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => 'item2',
                'resource_amount' => 200,
            ],
            [
                'mst_user_level_bonus_group_id' => 'usrLevelBonusGroup1',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => 'item3',
                'resource_amount' => 300,
            ],
        ]);
        MstItem::factory()->createMany([
            ['id' => 'item1'],
            ['id' => 'item2'],
            ['id' => 'item3'],
        ]);
        $param = [
            'usrMessageIds' => [
                'dcb649ef-4e66-14f6-33bd-29da50f2936f',
            ],
        ];

        // Exercise
        $response = $this->sendRequest('receive', $param);

        // Verify
        //  ステータスチェック
        $response->assertStatus(HttpStatusCode::SUCCESS);

        // レスポンス内容確認
        $responseJson = $response->json();

        $this->assertArrayHasKey('usrConditionPacks', $responseJson);

        $this->assertArrayHasKey('messageRewards', $responseJson);
        $actuals = collect($responseJson['messageRewards']);
        $this->assertCount(3, $actuals);
        $actuals = $actuals->map(function ($actual) {
            return $actual['reward'];
        });
        $actual = $actuals->filter(function ($actual) {
            return $actual['resourceType'] === RewardType::FREE_DIAMOND->value;
        })->first();
        $this->assertEquals(RewardType::FREE_DIAMOND->value, $actual['resourceType']);
        $this->assertEquals('1', $actual['resourceId']);
        $this->assertEquals(1, $actual['resourceAmount']);
        $actual = $actuals->filter(function ($actual) {
            return $actual['resourceType'] === RewardType::ITEM->value;
        })->first();
        $this->assertEquals(RewardType::ITEM->value, $actual['resourceType']);
        $this->assertEquals('item1', $actual['resourceId']);
        $this->assertEquals(100, $actual['resourceAmount']);
        $actual = $actuals->filter(function ($actual) {
            return $actual['resourceType'] === RewardType::EXP->value;
        })->first();
        $this->assertEquals(RewardType::EXP->value, $actual['resourceType']);
        $this->assertEquals(null, $actual['resourceId']);
        $this->assertEquals(100, $actual['resourceAmount']);

        $this->assertArrayHasKey('isEmblemDuplicated', $responseJson);

        $this->assertArrayHasKey('usrParameter', $responseJson);
        $actual = $responseJson['usrParameter'];
        $this->assertEquals(100, $actual['exp']);
        $this->assertEquals(0, $actual['coin']);
        $this->assertEquals(1, $actual['freeDiamond']);

        $this->assertArrayHasKey('usrItems', $responseJson);
        $actual = collect($responseJson['usrItems'])->keyBy('mstItemId');
        $this->assertCount(3, $actual);
        $this->assertEquals(100, $actual->get('item1')['amount']);
        $this->assertEquals(200, $actual->get('item2')['amount']);
        $this->assertEquals(300, $actual->get('item3')['amount']);

        $this->assertArrayHasKey('userLevel', $responseJson);
        $actual = $responseJson['userLevel'];
        $this->assertEquals(0, $actual['beforeExp']);
        $this->assertEquals(100, $actual['afterExp']);
        $this->assertCount(2, $actual['usrLevelReward']);
        $actuals = collect($actual['usrLevelReward'])
            ->map(function ($actual) {
                return $actual['reward'];
            })
            ->keyBy('resourceId');
        $this->assertEquals(RewardType::ITEM->value, $actuals->get('item2')['resourceType']);
        $this->assertEquals(200, $actuals->get('item2')['resourceAmount']);
        $this->assertEquals(RewardType::ITEM->value, $actuals->get('item3')['resourceType']);
        $this->assertEquals(300, $actuals->get('item3')['resourceAmount']);

        // DB確認

        // 報酬付与がされている。exp付与によってレベルアップ報酬も付与されている
        $usrUserParameter->refresh();
        $this->assertEquals(100, $usrUserParameter->getExp());
        $this->assertEquals(3, $usrUserParameter->getLevel());
        $this->assertEquals(0, $usrUserParameter->getCoin());

        $diamond = $this->getDiamond($userId);
        $this->assertEquals(1, $diamond->getFreeAmount());

        $usrItems = UsrItem::where('usr_user_id', $userId)->get()
            ->keyBy(function (UsrItem $usrItem) {
                return $usrItem->getMstItemId();
            });
        $this->assertEquals(100, $usrItems->get('item1')->getAmount());
        $this->assertEquals(200, $usrItems->get('item2')->getAmount());
        $this->assertEquals(300, $usrItems->get('item3')->getAmount());
    }
}
