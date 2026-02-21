<?php

namespace Feature\Domain\Item\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstItemEntity;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Repositories\MstQuestRepository;
use App\Domain\Stage\Constants\StageConstant;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Unit\Enums\UnitColorType;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MstQuestRepositoryTest extends TestCase
{
    private MstQuestRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = app(MstQuestRepository::class);
    }

    public static function parmas_test_getQuestGracePeriod_クエストの猶予期間を考慮してデータ取得できる()
    {
        return [
            'クエスト期間内_猶予' => ['now' => '2030-12-21 12:34:55', 'addGrace' => false, 'isThrowError' => false],
        ];
    }

    #[DataProvider('parmas_test_getQuestGracePeriod_クエストの猶予期間を考慮してデータ取得できる')]
    public function test_getQuestGracePeriod_クエストの猶予期間を考慮してデータ取得できる(
        string $now,
        bool $addGrace,
        bool $isThrowError,
    ) {
        // Setup
        $mstQuestId = 'quest1';
        $now = $this->fixTime($now);

        MstQuest::factory()->create([
            'id' => $mstQuestId,
            'quest_type' => QuestType::EVENT->value,
            'start_date' => '2022-01-01 00:00:00',
            'end_date' => '2030-12-21 12:34:56',
        ])->toEntity();

        if ($isThrowError) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode(ErrorCode::QUEST_PERIOD_OUTSIDE);
        }

        // Exercise
        $this->repository->getQuestGracePeriod($mstQuestId, $now, true);

        // Verify
        $this->assertTrue(True);
    }
}
