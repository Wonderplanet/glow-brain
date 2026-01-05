<?php

namespace Tests\Feature\Domain\User\Repositories;

use App\Domain\User\Models\UsrUserProfile;
use App\Domain\User\Repositories\UsrUserProfileRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UsrUserProfileRepositoryTest extends TestCase
{
    private UsrUserProfileRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(UsrUserProfileRepository::class);
    }

    public static function params_isDuplicateMyId(): array
    {
        return [
            '重複していない' => [
                'generatedMyId' => 'A1000000000',
                'newMyId' => 'A1000000001',
                'expected' => false,
            ],
            '重複している' => [
                'generatedMyId' => 'A1000000002',
                'newMyId' => 'A1000000002',
                'expected' => true,
            ],
        ];
    }

    #[DataProvider('params_isDuplicateMyId')]
    public function test_isDuplicateMyId(string $generatedMyId, string $newMyId, bool $expected)
    {
        UsrUserProfile::factory()->create(['my_id' => $generatedMyId]);

        // Exercise
        $actual = $this->execPrivateMethod($this->repository, 'isDuplicateMyId', [$newMyId]);

        // Verify
        $this->assertEquals($expected, $actual);
    }
}
