<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Pvp\Repositories;

use App\Domain\Pvp\Enums\PvpSessionStatus;
use App\Domain\Pvp\Repositories\UsrPvpSessionRepository;
use Tests\TestCase;

class UsrPvpSessionRepositoryTest extends TestCase
{
    private UsrPvpSessionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(UsrPvpSessionRepository::class);
    }

    public function testCreate_新しいPVPセッションを作成できることを確認する(): void
    {
        // Arrange
        $usrUserId = 'user_123';
        $this->setUsrUserId($usrUserId); // ユーザーIDを設定
        $sysPvpSeasonId = 'season_456';

        // Act
        $result = $this->repository->create($usrUserId, $sysPvpSeasonId);

        // Assert
        $this->assertEquals($usrUserId, $result->getUsrUserId());
    }

    public function testFindByUsrUserId_ユーザーIDでPVPセッションを取得できることを確認する(): void
    {
        // Arrange
        $usrUserId = 'user_123';
        $this->setUsrUserId($usrUserId); // ユーザーIDを設定
        $sysPvpSeasonId = 'season_456';

        // セッションを作成
        $this->repository->create($usrUserId, $sysPvpSeasonId);

        // Act
        $result = $this->repository->findByUsrUserId($usrUserId);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($usrUserId, $result->getUsrUserId());
    }

    public function testFindByUsrUserId_存在しないユーザーIDの場合nullが返ることを確認する(): void
    {
        // Arrange
        $nonExistentUserId = 'non_existent_user';

        // Act
        $result = $this->repository->findByUsrUserId($nonExistentUserId);

        // Assert
        $this->assertNull($result);
    }

    public function testFindOrCreate_既存のセッションが存在する場合はそれを返すことを確認する(): void
    {
        // Arrange
        $usrUserId = 'user_123';
        $this->setUsrUserId($usrUserId); // ユーザーIDを設定
        $sysPvpSeasonId = 'season_456';

        // 既存のセッションを作成
        $existingSession = $this->repository->create($usrUserId, $sysPvpSeasonId);

        // Act
        $result = $this->repository->findOrCreate($usrUserId, $sysPvpSeasonId);

        // Assert
        $this->assertEquals($existingSession->getUsrUserId(), $result->getUsrUserId());
    }

    public function testFindOrCreate_セッションが存在しない場合は新しく作成することを確認する(): void
    {
        // Arrange
        $usrUserId = 'user_123';
        $this->setUsrUserId($usrUserId); // ユーザーIDを設定
        $sysPvpSeasonId = 'season_456';

        // Act
        $result = $this->repository->findOrCreate($usrUserId, $sysPvpSeasonId);

        // Assert
        $this->assertEquals($usrUserId, $result->getUsrUserId());
        $this->assertEquals(PvpSessionStatus::CLOSED, $result->getIsValid());
    }
}
