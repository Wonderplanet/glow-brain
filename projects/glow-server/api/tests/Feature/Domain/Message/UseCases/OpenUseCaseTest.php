<?php

namespace Feature\Domain\Message\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Message\Models\Eloquent\UsrMessage;
use App\Domain\Message\Repositories\UsrMessageRepository;
use App\Domain\Message\Services\UsrMessageService;
use App\Domain\Message\UseCases\OpenUseCase;
use Carbon\CarbonImmutable;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;

class OpenUseCaseTest extends TestCase
{
    private OpenUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = app()->make(OpenUseCase::class);
    }

    public function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_exec_既読更新チェック(): void
    {
        // Setup
        CarbonImmutable::setTestNow('2020-01-15 00:00:00');
        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        //  すでに既読済み
        UsrMessage::factory()->createMany([
            [
                'id' => 'usr_message_1',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => 'message_1',
                'opened_at' => '2020-01-14 12:00:00',
            ],
            //  未読
            [
                'id' => 'usr_message_2',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => 'message_2',
            ],
            [
                'id' => 'usr_message_3',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => 'message_3',
            ],
            //  未読だが指定されてない
            [
                'id' => 'usr_message_4',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => 'message_4',
            ]
        ]);
        $messageIds = [
            'usr_message_1',
            'usr_message_2',
            'usr_message_3',
        ];

        // Exercise
        $this->useCase->exec($currentUser, $messageIds);
        $this->saveAll();

        // Verify
        //  更新データチェック
        $usrMessages = UsrMessage::query()->where('usr_user_id', $currentUser->getUsrUserId())->get();
        //   既読済みなので更新されない
        $message1UsrMessage = $usrMessages->firstWhere('mng_message_id', 'message_1');
        $this->assertEquals('2020-01-14 12:00:00', $message1UsrMessage->getOpenedAt());
        //   既読更新された
        $message2UsrMessage = $usrMessages->firstWhere('mng_message_id', 'message_2');
        $this->assertEquals('2020-01-15 00:00:00', $message2UsrMessage->getOpenedAt());
        $message3UsrMessage = $usrMessages->firstWhere('mng_message_id', 'message_3');
        $this->assertEquals('2020-01-15 00:00:00', $message3UsrMessage->getOpenedAt());
        //   未読のまま
        $message4UsrMessage = $usrMessages->firstWhere('mng_message_id', 'message_4');
        $this->assertNull($message4UsrMessage->getOpenedAt());
    }

    public function test_exec_グループ既読更新チェック(): void
    {
        // Setup
        CarbonImmutable::setTestNow('2020-01-15 00:00:00');
        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        //  すでに既読済み
        UsrMessage::factory()->createMany([
            [
                'id' => 'usr_message_1',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => 'message_1',
                'opened_at' => '2020-01-14 12:00:00',
            ],
            //  未読
            [
                'id' => 'usr_message_2',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => 'message_2',
                'reward_group_id' => 'group_1',
            ],
            [
                'id' => 'usr_message_3',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => 'message_3',
            ],
            //  未読だが指定されてない
            [
                'id' => 'usr_message_4',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => 'message_4',
                'reward_group_id' => 'group_1',
            ]
        ]);
        $messageIds = [
            'usr_message_1',
            'usr_message_2',
            'usr_message_3',
        ];

        // Exercise
        $this->useCase->exec($currentUser, $messageIds);
        $this->saveAll();

        // Verify
        //  更新データチェック
        $usrMessages = UsrMessage::query()->where('usr_user_id', $currentUser->getUsrUserId())->get();
        //   既読済みなので更新されない
        $message1UsrMessage = $usrMessages->firstWhere('mng_message_id', 'message_1');
        $this->assertEquals('2020-01-14 12:00:00', $message1UsrMessage->getOpenedAt());
        //   既読更新された
        $message2UsrMessage = $usrMessages->firstWhere('mng_message_id', 'message_2');
        $this->assertEquals('2020-01-15 00:00:00', $message2UsrMessage->getOpenedAt());
        $message3UsrMessage = $usrMessages->firstWhere('mng_message_id', 'message_3');
        $this->assertEquals('2020-01-15 00:00:00', $message3UsrMessage->getOpenedAt());
        //   グループ既読された
        $message4UsrMessage = $usrMessages->firstWhere('mng_message_id', 'message_4');
        $this->assertEquals('2020-01-15 00:00:00', $message4UsrMessage->getOpenedAt());
    }

    public function test_exec_既読する対象がなかった(): void
    {
        // Setup
        CarbonImmutable::setTestNow('2020-01-15 00:00:00');
        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        //  すでに既読済み
        UsrMessage::factory()
            ->set('id', 'usr_message_1')
            ->set('usr_user_id', $currentUser->getUsrUserId())
            ->set('mng_message_id', 'message_1')
            ->set('opened_at', '2020-01-14 12:00:00')
            ->create();
        $messageIds = [
            'usr_message_1',
        ];

        // Exercise
        $this->useCase->exec($currentUser, $messageIds);
        $this->saveAll();

        // Verify
        //  更新データチェック
        $usrMessages = UsrMessage::query()->where('usr_user_id', $currentUser->getUsrUserId())->get();
        //   既読済みなので更新されない
        $message1UsrMessage = $usrMessages->firstWhere('mng_message_id', 'message_1');
        $this->assertEquals('2020-01-14 12:00:00', $message1UsrMessage->getOpenedAt());
    }

    public function test_exec_受取済みメッセージがリクエストされてもエラーにならない(): void
    {
        // Setup
        $this->fixTime('2020-01-15 00:00:00');
        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        //  異なる受取・既読状態のメッセージを作成
        UsrMessage::factory()->createMany([
            [
                'id' => 'usr_message_1',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => 'message_1',
                'received_at' => '2020-01-14 10:00:00', // 受取済み
                'opened_at' => '2020-01-14 11:00:00',   // 既読済み
                'is_received' => true,
            ],
            [
                'id' => 'usr_message_2', 
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => 'message_2',
                'received_at' => null,                   // 受取済みでない
                'opened_at' => '2020-01-14 12:00:00',   // 既読済み
                'is_received' => false,
            ],
            [
                'id' => 'usr_message_3',
                'usr_user_id' => $currentUser->getUsrUserId(),
                'mng_message_id' => 'message_3',
                'received_at' => null,                   // 受取済みでない
                'opened_at' => null,                     // 未読
                'is_received' => false,
            ],
        ]);
        $messageIds = [
            'usr_message_1', // 受取済み、既読済み
            'usr_message_2', // 受取済みでない、既読済み
            'usr_message_3', // 受取済みでない
        ];

        // Exercise - 例外が発生しないことを確認
        $this->useCase->exec($currentUser, $messageIds);
        $this->saveAll();

        // Verify
        $usrMessages = UsrMessage::query()->where('usr_user_id', $currentUser->getUsrUserId())->get();
        
        //   受取済み＋既読済みの場合はopened_atは更新されない
        $message1UsrMessage = $usrMessages->firstWhere('mng_message_id', 'message_1');
        $this->assertEquals('2020-01-14 11:00:00', $message1UsrMessage->getOpenedAt()); // 既存のopened_atが保持される
        $this->assertEquals('2020-01-14 10:00:00', $message1UsrMessage->getReceivedAt()); // received_atも変更されない
        $this->assertTrue((bool)$message1UsrMessage->is_received); // is_receivedも変更されない
        
        //   受取済みでない＋既読済みの場合はopened_atは更新されない
        $message2UsrMessage = $usrMessages->firstWhere('mng_message_id', 'message_2');
        $this->assertEquals('2020-01-14 12:00:00', $message2UsrMessage->getOpenedAt()); // 既存のopened_atが保持される
        $this->assertNull($message2UsrMessage->getReceivedAt()); // received_atはnullのまま
        $this->assertFalse((bool)$message2UsrMessage->is_received); // is_receivedもfalseのまま
        
        //   受取済みでない＋未読のメッセージは既読更新される
        $message3UsrMessage = $usrMessages->firstWhere('mng_message_id', 'message_3');
        $this->assertEquals('2020-01-15 00:00:00', $message3UsrMessage->getOpenedAt()); // 既読更新される
        $this->assertNull($message3UsrMessage->getReceivedAt()); // received_atはnullのまま
        $this->assertFalse((bool)$message3UsrMessage->is_received); // is_receivedもfalseのまま
    }

    public function test_exec_例外がthrowされた(): void
    {
        // Setup
        CarbonImmutable::setTestNow('2020-01-15 00:00:00');
        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getId());
        $usrMessage = UsrMessage::factory()
            ->set('id', 'usr_message_1')
            ->set('usr_user_id', $currentUser->getUsrUserId())
            ->set('mng_message_id', 'message_1')
            ->create();
        $messageIds = [
            'usr_message_1',
        ];
        //  syncModels実行時にエラーが発生するようにmockを設定
        $usrMessageRepositoryMock = \Mockery::mock(UsrMessageRepository::class);
        $usrMessageRepositoryMock
            ->shouldReceive('getByUserIdAndMessageIds')
            ->andReturn(collect([$usrMessage]));
        $usrMessageRepositoryMock->shouldReceive('syncModels')->andThrow(\Exception::class);
        $clock = app()->make(Clock::class);
        $usrMessageService = app()->make(UsrMessageService::class);
        $useCase = new OpenUseCase($usrMessageRepositoryMock, $usrMessageService, $clock);

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::FAILURE_UPDATE_BY_MESSAGE_OPENED_AT);
        $useCase->exec($currentUser, $messageIds);
    }
}
