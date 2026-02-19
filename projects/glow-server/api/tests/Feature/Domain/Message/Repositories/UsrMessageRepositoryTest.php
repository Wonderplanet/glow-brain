<?php

declare(strict_types=1);

namespace Feature\Domain\Message\Repositories;

use App\Domain\Message\Enums\MessageSource;
use App\Domain\Message\Models\Eloquent\UsrMessage;
use App\Domain\Message\Repositories\UsrMessageRepository;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class UsrMessageRepositoryTest extends TestCase
{
    private UsrMessageRepository $usrMessageRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->usrMessageRepository = $this->app->make(UsrMessageRepository::class);
    }

    public function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    /**
     * @test
     * @dataProvider initData
     */
    public function create_メッセージ作成チェック(
        ?CarbonImmutable $expiredAt,
        ?MessageSource $messageSource,
    ): void {
        // Setup
        CarbonImmutable::setTestNow('2020-01-15 00:00:00');
        $usrUser = $this->createUsrUser();
        $mngMessageId = fake()->uuid();

        // Exercise
        $this->usrMessageRepository
            ->create($usrUser->getId(), $mngMessageId, $messageSource?->value, $expiredAt);
        $this->saveAll();

        // Verify
        $result = UsrMessage::query()
            ->where('usr_user_id', $usrUser->getId())
            ->get()
            ->first();
        $this->assertEquals($usrUser->getId(), $result->getUsrUserId());
        $this->assertEquals($mngMessageId, $result->getMngMessageId());
        $this->assertEquals($messageSource?->value, $result->message_source);
        $this->assertNull($result->getOpenedAt());
        $this->assertNull($result->getReceivedAt());
        $this->assertEquals($expiredAt, $result->getExpiredAt());
    }

    /**
     * @return array
     */
    public static function initData(): array
    {
        return [
            '期限なし、messageSourceがnull' => [null, null],
            '期限あり、messageSourceがnull' => [CarbonImmutable::make('2020-01-01 00:00:00'), null],
            '期限なし、messageSourceに値あり' => [null, MessageSource::MNG_MESSAGE],
            '期限あり、messageSourceに値あり' => [CarbonImmutable::make('2020-01-01 00:00:00'), MessageSource::MNG_MESSAGE],
        ];
    }

    /**
     * @test
     */
    public function getByUserId_データ取得チェック(): void
    {
        // Setup
        $userId = '1';
        UsrMessage::factory()
            ->set('usr_user_id', $userId)
            ->set('mng_message_id', 'message_1')
            ->set('opened_at', '2020-01-01 00:00:00')
            ->set('received_at', '2020-01-10 00:00:00')
            ->set('expired_at', '2020-01-14 23:59:59')
            ->create();
        UsrMessage::factory()
            ->set('usr_user_id', $userId)
            ->set('mng_message_id', 'message_2')
            ->create();
        UsrMessage::factory()
            ->set('usr_user_id', $userId)
            ->set('mng_message_id', 'message_3')
            ->create();
        // 別ユーザー
        UsrMessage::factory()
            ->set('usr_user_id', '2')
            ->set('mng_message_id', 'message_1')
            ->create();

        // Exercise
        $userMessages = $this->usrMessageRepository
            ->getByUserId($userId);

        // Verify
        //  想定した件数が取得できているかチェック
        $this->assertCount(3, $userMessages);
    }

    /**
     * @test
     */
    public function getReceivableList_取得確認(): void
    {
        // Setup
        CarbonImmutable::setTestNow('2020-01-15 00:00:00');
        $userId = '1';
        //  取得可能 expired_atがnull
        $usrMessage1 = UsrMessage::factory()
            ->set('id', 'test1')
            ->set('usr_user_id', $userId)
            ->create();
        //  取得可能 expired_atが期間内
        $usrMessage2 = UsrMessage::factory()
            ->set('id', 'test2')
            ->set('usr_user_id', $userId)
            ->set('expired_at', '2020-01-15 00:00:00')
            ->create();
        // 取得不可 expired_atが期間外
        UsrMessage::factory()
            ->set('id', 'test3')
            ->set('usr_user_id', $userId)
            ->set('expired_at', '2020-01-14 23:59:59')
            ->create();
        // 取得不可 user_idが異なる
        UsrMessage::factory()
            ->set('id', 'test4')
            ->set('usr_user_id', fake()->uuid)
            ->set('expired_at', '2020-01-31 23:59:59')
            ->create();

        // Exercise
        $messages = $this->usrMessageRepository
            ->getReceivableList($userId, CarbonImmutable::now())
            ->keyBy(fn($row) => $row->getId());

        // Verify
        //  取得件数チェック
        $this->assertCount(2, $messages);
        //  中身のチェック
        $row1 = $messages->get('test1');
        $this->assertEquals($usrMessage1->usr_user_id, $row1->getUsrUserId());
        $this->assertEquals($usrMessage1->mng_message_id, $row1->getMngMessageId());
        $this->assertEquals($usrMessage1->message_source, $row1->getMessageSource());
        $this->assertEquals($usrMessage1->opened_at, $row1->getOpenedAt());
        $this->assertEquals($usrMessage1->received_at, $row1->getReceivedAt());
        $this->assertEquals($usrMessage1->expired_at, $row1->getExpiredAt());
        $row2 = $messages->get('test2');
        $this->assertEquals($usrMessage2->usr_user_id, $row2->getUsrUserId());
        $this->assertEquals($usrMessage2->mng_message_id, $row2->getMngMessageId());
        $this->assertEquals($usrMessage2->message_source, $row2->getMessageSource());
        $this->assertEquals($usrMessage2->opened_at, $row2->getOpenedAt());
        $this->assertEquals($usrMessage2->received_at, $row2->getReceivedAt());
        $this->assertEquals($usrMessage2->expired_at, $row2->getExpiredAt());
    }

    /**
     * @test
     */
    public function getByUserIdAndMessageIds_取得確認(): void
    {
        // Setup
        $userId = '1';
        //  取得対象
        $message1 = UsrMessage::factory()
            ->set('usr_user_id', $userId)
            ->set('mng_message_id', 'message_1')
            ->set('opened_at', '2020-01-14 12:00:00')
            ->create();
        $message2 = UsrMessage::factory()
            ->set('usr_user_id', $userId)
            ->set('mng_message_id', 'message_2')
            ->create();
        //  取得対象外
        UsrMessage::factory()
            ->set('usr_user_id', $userId)
            ->set('mng_message_id', 'message_3')
            ->create();
        $messageIds = [
            'message_1',
            'message_2',
        ];

        // Exercise
        $messages = $this->usrMessageRepository
            ->getByUserIdAndMessageIds($userId, $messageIds)
            ->keyBy(fn($row) => $row->getId());

        // Verify
        //  取得件数チェック
        $this->assertCount(2, $messages);
        //  中身のチェック
        $row1 = $messages->get($message1->id);
        $this->assertEquals($message1->usr_user_id, $row1->getUsrUserId());
        $this->assertEquals($message1->mng_message_id, $row1->getMngMessageId());
        $this->assertEquals($message1->message_source, $row1->getMessageSource());
        $this->assertEquals($message1->opened_at, $row1->getOpenedAt());
        $this->assertEquals($message1->received_at, $row1->getReceivedAt());
        $this->assertEquals($message1->expired_at, $row1->getExpiredAt());
        $row2 = $messages->get($message2->id);
        $this->assertEquals($message2->usr_user_id, $row2->getUsrUserId());
        $this->assertEquals($message2->mng_message_id, $row2->getMngMessageId());
        $this->assertEquals($message2->message_source, $row2->getMessageSource());
        $this->assertEquals($message2->opened_at, $row2->getOpenedAt());
        $this->assertEquals($message2->received_at, $row2->getReceivedAt());
        $this->assertEquals($message2->expired_at, $row2->getExpiredAt());
    }

    /**
     * @test
     */
    public function getUnopenedMessageCount_未読メッセージ数取得(): void
    {
        // Setup
        CarbonImmutable::setTestNow('2020-01-15 00:00:00');
        $userId = '1';
        $unopenedMessageCount = 2;
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
            ->set('expired_at', '2020-01-16 23:59:59')
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
        // Exercise
        $unopenedMessageCounts = $this->usrMessageRepository
            ->getUnopenedMessages($userId, CarbonImmutable::now())->count();

        $this->assertEquals($unopenedMessageCount, $unopenedMessageCounts);
    }
}
