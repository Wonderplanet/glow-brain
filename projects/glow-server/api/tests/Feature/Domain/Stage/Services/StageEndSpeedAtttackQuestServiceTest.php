<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Stage\Services;

use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Encyclopedia\Models\UsrArtworkFragment;
use App\Domain\InGame\Enums\InGameSpecialRuleType;
use App\Domain\Resource\Enums\InGameContentType;
use App\Domain\Resource\Mst\Models\MstArtwork;
use App\Domain\Resource\Mst\Models\MstArtworkFragment;
use App\Domain\Resource\Mst\Models\MstInGameSpecialRule;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Resource\Mst\Models\MstStageEventSetting;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Stage\Entities\StageInGameBattleLog;
use App\Domain\Stage\Models\UsrStageEvent;
use App\Domain\Stage\Models\UsrStageSession;
use App\Domain\Stage\Services\StageEndSpeedAtttackQuestService;
use App\Domain\User\Models\UsrUserParameter;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class StageEndSpeedAtttackQuestServiceTest extends TestCase
{
    private StageEndSpeedAtttackQuestService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(StageEndSpeedAtttackQuestService::class);
    }

    public function test_end_原画のかけらがドロップする(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $now = $this->fixTime('2026-03-03 12:00:00');
        $mstStageId = 'mstStageId';
        $mstQuestId = 'mstQuestId';
        $dropGroupId = 'dropGroup1';
        $mstArtworkId = 'artwork1';

        // マスタデータ
        MstQuest::factory()->create([
            'id' => $mstQuestId,
            'quest_type' => 'Event',
            'start_date' => '2023-01-01 00:00:00',
            'end_date' => '2037-01-01 00:00:00',
        ]);
        $mstStage = MstStage::factory()->create([
            'id' => $mstStageId,
            'mst_quest_id' => $mstQuestId,
            'exp' => 1,
            'coin' => 1000,
            'mst_artwork_fragment_drop_group_id' => $dropGroupId,
        ]);
        MstStageEventSetting::factory()->create([
            'mst_stage_id' => $mstStageId,
            'start_at' => $now->subDays(1)->toDateTimeString(),
            'end_at' => $now->addDays(1)->toDateTimeString(),
        ]);
        MstInGameSpecialRule::factory()->create([
            'content_type' => InGameContentType::STAGE,
            'target_id' => $mstStageId,
            'rule_type' => InGameSpecialRuleType::SPEED_ATTACK,
            'start_at' => $now->subDays(1)->toDateTimeString(),
            'end_at' => $now->addDays(1)->toDateTimeString(),
        ]);

        // 原画と原画のかけら（確定ドロップ）
        MstArtwork::factory()->create(['id' => $mstArtworkId]);
        MstArtworkFragment::factory()->createMany([
            ['id' => 'fragment1', 'mst_artwork_id' => $mstArtworkId, 'drop_group_id' => $dropGroupId, 'drop_percentage' => 100],
            ['id' => 'fragment2', 'mst_artwork_id' => $mstArtworkId, 'drop_group_id' => $dropGroupId, 'drop_percentage' => 100],
        ]);

        // ユーザーレベル・スタミナ
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 100],
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'stamina' => 10,
            'exp' => 0,
            'coin' => 0,
        ]);

        // ステージイベント進行
        UsrStageEvent::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => $mstStageId,
            'clear_count' => 1,
            'reset_clear_count' => 1,
            'reset_ad_challenge_count' => 0,
            'reset_clear_time_ms' => null,
            'clear_time_ms' => null,
            'latest_reset_at' => now(),
            'latest_event_setting_end_at' => '2000-01-01 00:00:00',
        ]);

        // ステージセッション（開始済み）
        $usrStageSession = UsrStageSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => $mstStageId,
            'is_valid' => 1,
        ]);

        $inGameBattleLog = new StageInGameBattleLog(
            defeatEnemyCount: 10,
            defeatBossEnemyCount: 1,
            score: 500,
            clearTimeMs: 15000,
            discoveredEnemyDataList: collect(),
            partyStatusList: collect(),
            artworkPartyStatusList: collect(),
        );

        // Exercise
        $this->service->end(
            $usrUserId,
            $mstStage->toEntity(),
            $usrStageSession,
            $inGameBattleLog,
            collect(),
            $now,
        );
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify - 原画のかけらが獲得されることを確認
        $usrArtworkFragments = UsrArtworkFragment::query()
            ->where('usr_user_id', $usrUserId)
            ->whereIn('mst_artwork_fragment_id', ['fragment1', 'fragment2'])
            ->get();
        $this->assertCount(2, $usrArtworkFragments);

        // 全かけらが集まったので原画も獲得されることを確認
        $usrArtworks = UsrArtwork::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_id', $mstArtworkId)
            ->get();
        $this->assertCount(1, $usrArtworks);
    }
}
