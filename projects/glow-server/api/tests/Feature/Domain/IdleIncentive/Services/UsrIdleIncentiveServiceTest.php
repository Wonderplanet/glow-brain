<?php

namespace Tests\Feature\Domain\IdleIncentive\Services;

use App\Domain\IdleIncentive\Models\UsrIdleIncentive;
use App\Domain\IdleIncentive\Services\UsrIdleIncentiveService;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Stage\Enums\QuestDifficulty;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UsrIdleIncentiveServiceTest extends TestCase
{
    private UsrIdleIncentiveService $usrIdleIncentiveService;

    public function setUp(): void
    {
        parent::setUp();

        $this->usrIdleIncentiveService = $this->app->make(UsrIdleIncentiveService::class);
    }

    #[DataProvider('params_resetDiamondQuickReceiveCount_日跨ぎリセットできてることを確認')]
    public function test_resetDiamondQuickReceiveCount_日跨ぎリセットできてることを確認(
        string $beforeAt,
        string $now,
        int $expected
    ) {
        // Setup
        $this->fixTime($now);

        $usrIdleIncentive = UsrIdleIncentive::factory()->create([
            'diamond_quick_receive_count' => 2,
            'diamond_quick_receive_at' => $beforeAt,
        ]);

        // Exercise
        $this->usrIdleIncentiveService->resetDiamondQuickReceiveCount(
            $usrIdleIncentive,
        );

        // Verify
        $this->assertEquals($expected, $usrIdleIncentive->getDiamondQuickReceiveCount());
    }

    public static function params_resetDiamondQuickReceiveCount_日跨ぎリセットできてることを確認()
    {
        // DBにはUTCを保存するので、UTCでパラメータを作っています
        return [
            '日跨ぎして、リセットされる' => [
                'beforeAt' => "2024-01-01 05:00:00",
                'now' => "2024-01-02 05:00:00",
                'expected' => 0,
            ],
            '日跨ぎ時間ちょうど リセットされる' => [
                'beforeAt' => "2024-01-01 18:59:59",
                'now' => "2024-01-01 19:00:00",
                'expected' => 0,
            ],
            '日跨しておらず、リセットされない' => [
                'beforeAt' => "2024-01-01 00:00:00",
                'now' => "2024-01-01 01:00:00",
                'expected' => 2,
            ],

        ];
    }

    #[DataProvider('params_resetAdQuickReceiveCount_日跨ぎリセットできてることを確認')]
    public function test_resetAdQuickReceiveCount_日跨ぎリセットできてることを確認(
        string $beforeAt,
        string $now,
        int $expected
    ) {
        // Setup
        $this->fixTime($now);

        $usrIdleIncentive = UsrIdleIncentive::factory()->create([
            'ad_quick_receive_count' => 2,
            'ad_quick_receive_at' => $beforeAt,
        ]);

        // Exercise
        $this->usrIdleIncentiveService->resetAdQuickReceiveCount(
            $usrIdleIncentive,
        );

        // Verify
        $this->assertEquals($expected, $usrIdleIncentive->getAdQuickReceiveCount());
    }

    public static function params_resetAdQuickReceiveCount_日跨ぎリセットできてることを確認()
    {
        return [
            '日跨ぎして、リセットされる' => [
                'beforeAt' => "2024-01-01 05:00:00",
                'now' => "2024-01-02 05:00:00",
                'expected' => 0,
            ],
            '日跨ぎ時間ちょうど リセットされる' => [
                'beforeAt' => "2024-01-01 18:59:59",
                'now' => "2024-01-01 19:00:00",
                'expected' => 0,
            ],
            '日跨しておらず、リセットされない' => [
                'beforeAt' => "2024-01-01 00:00:00",
                'now' => "2024-01-01 01:00:00",
                'expected' => 2,
            ],
        ];
    }

    public function test_diamondQuickReceive_ユーザーステータスを変更できている()
    {
        // Setup
        $now = CarbonImmutable::now();

        $usrIdleIncentive = UsrIdleIncentive::factory()->create([
            'diamond_quick_receive_count' => 1,
            'diamond_quick_receive_at' => '2020-01-01 00:00:00',
        ]);

        // Exercise
        $this->usrIdleIncentiveService->diamondQuickReceive(
            $usrIdleIncentive,
            $now,
        );

        // Verify
        $this->assertEquals(2, $usrIdleIncentive->getDiamondQuickReceiveCount());
        $this->assertEquals($now->toDateTimeString(), $usrIdleIncentive->getDiamondQuickReceiveAt());
    }

    public function test_adQuickReceive_ユーザーステータスを変更できている()
    {
        // Setup
        $now = CarbonImmutable::now();

        $usrIdleIncentive = UsrIdleIncentive::factory()->create([
            'ad_quick_receive_count' => 1,
            'ad_quick_receive_at' => '2020-01-01 00:00:00',
        ]);

        // Exercise
        $this->usrIdleIncentiveService->adQuickReceive(
            $usrIdleIncentive,
            $now,
        );

        // Verify
        $this->assertEquals(2, $usrIdleIncentive->getAdQuickReceiveCount());
        $this->assertEquals($now->toDateTimeString(), $usrIdleIncentive->getAdQuickReceiveAt());
    }

    #[DataProvider('params_updateRewardMstStageId_更新対象の条件確認')]
    public function test_updateRewardMstStageId_更新対象の条件確認(
        string $questType,
        string $questDifficulty,
        ?string $currentRewardMstStageId,
        int $currentSortOrder,
        int $newSortOrder,
        bool $shouldUpdate,
        ?string $expectedRewardMstStageId
    ) {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $now = $this->fixTime();

        // 既存のQuest/Stageデータ
        MstQuest::factory()->create([
            'id' => 'currentQuest',
            'quest_type' => QuestType::NORMAL->value,
            'difficulty' => QuestDifficulty::NORMAL->value,
        ]);
        MstStage::factory()->create([
            'id' => 'currentStage',
            'mst_quest_id' => 'currentQuest',
            'sort_order' => $currentSortOrder,
        ]);

        // 新しいQuest/Stageデータ
        MstQuest::factory()->create([
            'id' => 'newQuest',
            'quest_type' => $questType,
            'difficulty' => $questDifficulty,
        ]);
        MstStage::factory()->create([
            'id' => 'newStage',
            'mst_quest_id' => 'newQuest',
            'sort_order' => $newSortOrder,
        ]);

        // currentRewardMstStageIdがnullでない場合のみ事前レコードを作成
        if ($currentRewardMstStageId !== null) {
            UsrIdleIncentive::factory()->create([
                'usr_user_id' => $usrUserId,
                'reward_mst_stage_id' => $currentRewardMstStageId,
            ]);
        }

        // Exercise
        $this->usrIdleIncentiveService->updateRewardMstStageId(
            $usrUserId,
            'newStage',
            $now
        );
        $this->saveAll();

        // Verify
        $usrIdleIncentive = UsrIdleIncentive::where('usr_user_id', $usrUserId)->first();
        $actualRewardMstStageId = $usrIdleIncentive->getRewardMstStageId();

        if ($shouldUpdate) {
            $this->assertEquals($expectedRewardMstStageId, $actualRewardMstStageId);
        } else {
            $this->assertEquals($currentRewardMstStageId, $actualRewardMstStageId);
        }
    }

    public static function params_updateRewardMstStageId_更新対象の条件確認()
    {
        return [
            'Normal難易度のメインクエスト_現在nullで新ステージ進捗_更新される' => [
                'questType' => QuestType::NORMAL->value,
                'questDifficulty' => QuestDifficulty::NORMAL->value,
                'currentRewardMstStageId' => null,
                'currentSortOrder' => 1,
                'newSortOrder' => 2,
                'shouldUpdate' => true,
                'expectedRewardMstStageId' => 'newStage',
            ],
            'Normal難易度のメインクエスト_新ステージの方が進捗_更新される' => [
                'questType' => QuestType::NORMAL->value,
                'questDifficulty' => QuestDifficulty::NORMAL->value,
                'currentRewardMstStageId' => 'currentStage',
                'currentSortOrder' => 1,
                'newSortOrder' => 2,
                'shouldUpdate' => true,
                'expectedRewardMstStageId' => 'newStage',
            ],
            'Normal難易度のメインクエスト_新ステージの方が進捗が低い_更新されない' => [
                'questType' => QuestType::NORMAL->value,
                'questDifficulty' => QuestDifficulty::NORMAL->value,
                'currentRewardMstStageId' => 'currentStage',
                'currentSortOrder' => 2,
                'newSortOrder' => 1,
                'shouldUpdate' => false,
                'expectedRewardMstStageId' => 'currentStage',
            ],
            'Normal難易度のメインクエスト_同じ進捗_更新されない' => [
                'questType' => QuestType::NORMAL->value,
                'questDifficulty' => QuestDifficulty::NORMAL->value,
                'currentRewardMstStageId' => 'currentStage',
                'currentSortOrder' => 2,
                'newSortOrder' => 2,
                'shouldUpdate' => false,
                'expectedRewardMstStageId' => 'currentStage',
            ],
            'Tutorialクエスト_新ステージの方が進捗_更新される' => [
                'questType' => QuestType::TUTORIAL->value,
                'questDifficulty' => QuestDifficulty::NORMAL->value,
                'currentRewardMstStageId' => 'currentStage',
                'currentSortOrder' => 1,
                'newSortOrder' => 2,
                'shouldUpdate' => true,
                'expectedRewardMstStageId' => 'newStage',
            ],
            'Hard難易度のメインクエスト_更新されない' => [
                'questType' => QuestType::NORMAL->value,
                'questDifficulty' => QuestDifficulty::HARD->value,
                'currentRewardMstStageId' => 'currentStage',
                'currentSortOrder' => 1,
                'newSortOrder' => 2,
                'shouldUpdate' => false,
                'expectedRewardMstStageId' => 'currentStage',
            ],
            'Extra難易度のメインクエスト_更新されない' => [
                'questType' => QuestType::NORMAL->value,
                'questDifficulty' => QuestDifficulty::EXTRA->value,
                'currentRewardMstStageId' => 'currentStage',
                'currentSortOrder' => 1,
                'newSortOrder' => 2,
                'shouldUpdate' => false,
                'expectedRewardMstStageId' => 'currentStage',
            ],
            'イベントクエスト_更新されない' => [
                'questType' => QuestType::EVENT->value,
                'questDifficulty' => QuestDifficulty::NORMAL->value,
                'currentRewardMstStageId' => 'currentStage',
                'currentSortOrder' => 1,
                'newSortOrder' => 2,
                'shouldUpdate' => false,
                'expectedRewardMstStageId' => 'currentStage',
            ],
        ];
    }

    /**
     * クリア順的に、チュートリアル → メインクエスト の順になるが、
     * mst_stages.sort_orderの値が、メインクエストのステージより、チュートリアルクエストのステージの値の方が大きいと、
     * 一生更新されないバグケースになってしまう。
     * 上記のバグが発生しないことを確認するためのテストケース。
     */
    public function test_updateRewardMstStageId_チュートリアル全クリアあとのメインクエスト初回クリア時に更新がある()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $now = $this->fixTime();

        // 既存のQuest/Stageデータ
        MstQuest::factory()->create([
            'id' => 'currentQuest',
            'quest_type' => QuestType::TUTORIAL->value,
            'difficulty' => QuestDifficulty::NORMAL->value,
        ]);
        MstStage::factory()->create([
            'id' => 'currentStage',
            'mst_quest_id' => 'currentQuest',
            'sort_order' => 9999,
        ]);

        // 新しいQuest/Stageデータ
        MstQuest::factory()->create([
            'id' => 'newQuest',
            'quest_type' => QuestType::NORMAL->value,
            'difficulty' => QuestDifficulty::NORMAL->value,
        ]);
        MstStage::factory()->create([
            'id' => 'newStage',
            'mst_quest_id' => 'newQuest',
            'sort_order' => 1,
        ]);

        UsrIdleIncentive::factory()->create([
            'usr_user_id' => $usrUserId,
            'reward_mst_stage_id' => 'currentStage',
        ]);

        // Exercise
        $this->usrIdleIncentiveService->updateRewardMstStageId(
            $usrUserId,
            'newStage',
            $now
        );
        $this->saveAll();

        // Verify
        $usrIdleIncentive = UsrIdleIncentive::where('usr_user_id', $usrUserId)->first();
        $actualRewardMstStageId = $usrIdleIncentive->getRewardMstStageId();
        $this->assertEquals('newStage', $actualRewardMstStageId);
    }
}
