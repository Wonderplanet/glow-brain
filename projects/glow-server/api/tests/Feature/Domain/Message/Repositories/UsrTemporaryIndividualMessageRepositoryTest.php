<?php

declare(strict_types=1);

namespace Feature\Domain\Message\Repositories;

use App\Domain\Message\Models\UsrTemporaryIndividualMessage;
use App\Domain\Message\Models\UsrTemporaryIndividualMessageInterface;
use App\Domain\Message\Repositories\UsrTemporaryIndividualMessageRepository;
use Tests\TestCase;

class UsrTemporaryIndividualMessageRepositoryTest extends TestCase
{
    private UsrTemporaryIndividualMessageRepository $usrTemporaryIndividualMessageRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->usrTemporaryIndividualMessageRepository =
            $this->app->make(UsrTemporaryIndividualMessageRepository::class);
    }

    /**
     * @test
     */
    public function getByUserIdAndMngMessageIds_データ取得チェック(): void
    {
        // Setup
        $usrUserId = 'user-1';
        $mngMessageIds = ['message-1', 'message-2', 'message-3'];
        foreach ($mngMessageIds as $mngMessageId) {
            UsrTemporaryIndividualMessage::factory()
                ->set('usr_user_id', $usrUserId)
                ->set('mng_message_id', $mngMessageId)
                ->create();
        }

        // Exercise
        $result = $this->usrTemporaryIndividualMessageRepository
            ->getByUserIdAndMngMessageIds($usrUserId, $mngMessageIds);

        // Verify
        //  件数チェック
        $this->assertCount(3, $result);
        //  想定したmngMessageIdが取得できているかチェック
        $resultMngMessageIds = $result->map(
            fn(UsrTemporaryIndividualMessageInterface $row) => $row->getMngMessageId()
        )->toArray();
        $this->assertEquals($mngMessageIds, $resultMngMessageIds);
    }
}
