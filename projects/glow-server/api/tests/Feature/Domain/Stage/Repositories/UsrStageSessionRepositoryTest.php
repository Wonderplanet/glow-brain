<?php

namespace Tests\Feature\Domain\Stage\Repositories;

use App\Domain\Stage\Enums\StageSessionStatus;
use App\Domain\Stage\Models\UsrStageSession;
use App\Domain\Stage\Repositories\UsrStageSessionRepository;
use Tests\TestCase;

class UsrStageSessionRepositoryTest extends TestCase
{
    private UsrStageSessionRepository $usrStageSessionRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usrStageSessionRepository = app(UsrStageSessionRepository::class);
    }

    /**
     * @test
     */
    public function get_レコードがない場合は作成して返すことを確認する()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->id;
        $now = $this->fixTime();

        // Exercise
        $usrStageSession = $this->usrStageSessionRepository->get($usrUserId, $now);

        // Verify
        $this->assertInstanceOf(UsrStageSession::class, $usrStageSession);
        $this->assertEquals(StageSessionStatus::CLOSED, $usrStageSession->getIsValid());
    }
}
