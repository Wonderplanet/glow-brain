<?php

namespace Tests\Feature\Domain\Pvp;

use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Pvp\Enums\PvpSessionStatus;
use App\Domain\Pvp\Models\UsrPvp;
use App\Domain\Pvp\Models\UsrPvpSession;
use App\Domain\Pvp\Services\PvpCacheService;
use App\Domain\Pvp\Services\PvpMatchingService;
use App\Domain\Resource\Mst\Models\MstPvpRank;
use App\Domain\Resource\Mst\Models\MstPvp;
use App\Domain\Resource\Mst\Models\MstDummyUser;
use App\Domain\Resource\Mst\Models\MstDummyUserI18n;
use App\Domain\Resource\Mst\Models\MstPvpDummy;
use App\Domain\Resource\Mst\Models\MstDummyUserUnit;
use App\Domain\Resource\Mst\Models\MstPvpMatchingScoreRange;
use App\Domain\Resource\Mst\Models\MstDummyOutpost;
use App\Domain\Resource\Mst\Models\MstOutpostEnhancement;
use App\Http\Responses\Data\OpponentPvpStatusData;
use App\Http\Responses\Data\OpponentSelectStatusData;
use App\Http\Responses\Data\PvpUnitData;
use App\Domain\Pvp\Entities\PvpEncyclopediaEffect;
use App\Domain\Pvp\Enums\PvpBonusType;
use App\Domain\Pvp\Enums\PvpMatchingType;
use App\Domain\Resource\Mst\Models\MstPvpBonusPoint;
use App\Domain\User\Models\UsrUserProfile;
use Tests\TestCase;

class PvpMatchingServiceTest extends TestCase
{
    private PvpMatchingService $pvpMatchingService;
    private PvpCacheService $pvpCacheService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pvpMatchingService = $this->app->make(PvpMatchingService::class);
        $this->pvpCacheService = $this->app->make(PvpCacheService::class);
    }

    public function test_getMatchingOpponentSelectStatusDatas正常(): void
    {
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();

        $sysPvpSeasonId = sprintf(
            '%04d0%02d',
            $now->isoWeekYear,
            $now->isoWeek
        );

        UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'score' => 25,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 3,
        ]);
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'is_valid' => PvpSessionStatus::STARTED,
        ]);

        $this->setDummyUser();
        $this->setPvpData();
        $this->setMstPvpMatchingScoreRange();

        foreach ($this->setUserCacheBaseParam($sysPvpSeasonId) as $value) {
            $this->pvpCacheService->addOpponentCandidate(
                $value['sys_pvp_season_id'],
                $value['my_id'],
                $value['pvp_rank_class_type'],
                $value['pvp_rank_class_level'],
                $value['score']
            );
            $this->setUserCache($value['sys_pvp_season_id'], $value['my_id'], $value['score']);
        }
        // 抽選を実行
        $exec = $this->pvpMatchingService->getMatchingOpponentSelectStatusDatas($usrUserId, $sysPvpSeasonId, $now);

        // キャッシュがあればキャッシュからデータがとれていること 
        // 格上はcacheUserId5　同格はcacheUserId4、格下はcacheUserId1が返るように設定
        $this->assertEquals('cacheUserId5', $exec[0]->getPvpUserProfile()->getMyId());
        $this->assertEquals('cacheUserId4', $exec[1]->getPvpUserProfile()->getMyId());
        $this->assertEquals('cacheUserId1', $exec[2]->getPvpUserProfile()->getMyId());
    }

    public function test_getMatchingOpponentSelectStatusDatasキャッシュなし(): void
    {
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();
        $sysPvpSeasonId = sprintf(
            '%04d0%02d',
            $now->isoWeekYear,
            $now->isoWeek
        );

        UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'score' => 1,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 3,
        ]);
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'is_valid' => PvpSessionStatus::STARTED,
        ]);

        $this->setDummyUser();
        $this->setPvpData();
        $this->setMstPvpMatchingScoreRange();

        // 抽選を実行
        $exec = $this->pvpMatchingService->getMatchingOpponentSelectStatusDatas($usrUserId, $sysPvpSeasonId, $now);

        // キャッシュがなければダミーが選出される
        $this->assertStringContainsString('dummyUserId', $exec[0]->getPvpUserProfile()->getMyId());
        $this->assertStringContainsString('dummyUserId', $exec[1]->getPvpUserProfile()->getMyId());
        $this->assertStringContainsString('dummyUserId', $exec[2]->getPvpUserProfile()->getMyId());

        $myIds = [
            $exec[0]->getPvpUserProfile()->getMyId(),
            $exec[1]->getPvpUserProfile()->getMyId(),
            $exec[2]->getPvpUserProfile()->getMyId(),
        ];
        $this->assertEquals(3, count($myIds));
    }


    public function test_getMatchingOpponentSelectStatusDatas格上キャッシュなし(): void
    {
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();

        $sysPvpSeasonId = sprintf(
            '%04d0%02d',
            $now->isoWeekYear,
            $now->isoWeek
        );

        UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'score' => 25,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 3,
        ]);
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'is_valid' => PvpSessionStatus::STARTED,
        ]);

        $this->setDummyUser();
        $this->setPvpData();
        $this->setMstPvpMatchingScoreRange();

        foreach ($this->setUserCacheBaseParam2($sysPvpSeasonId) as $value) {
            $this->pvpCacheService->addOpponentCandidate(
                $value['sys_pvp_season_id'],
                $value['my_id'],
                $value['pvp_rank_class_type'],
                $value['pvp_rank_class_level'],
                $value['score']
            );
            $this->setUserCache($value['sys_pvp_season_id'], $value['my_id'], $value['score']);
        }
        // 抽選を実行
        $exec = $this->pvpMatchingService->getMatchingOpponentSelectStatusDatas($usrUserId, $sysPvpSeasonId, $now);

        // キャッシュがあればキャッシュからデータがとれていること 
        // 格上のキャッシュを作っていないのでダミーのIDが取れる
        $this->assertStringContainsString('dummyUserId', $exec[0]->getPvpUserProfile()->getMyId());
        $this->assertEquals('cacheUserId4', $exec[1]->getPvpUserProfile()->getMyId());
        $this->assertEquals('cacheUserId1', $exec[2]->getPvpUserProfile()->getMyId());
    }

    public function test_getMatchingOpponentSelectStatusDatas格下キャッシュなし(): void
    {
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();
        $sysPvpSeasonId = sprintf(
            '%04d0%02d',
            $now->isoWeekYear,
            $now->isoWeek
        );

        UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'score' => 25,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 3,
        ]);
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'is_valid' => PvpSessionStatus::STARTED,
        ]);

        $this->setDummyUser();
        $this->setPvpData();
        $this->setMstPvpMatchingScoreRange();

        foreach ($this->setUserCacheBaseParam3($sysPvpSeasonId) as $value) {
            $this->pvpCacheService->addOpponentCandidate(
                $value['sys_pvp_season_id'],
                $value['my_id'],
                $value['pvp_rank_class_type'],
                $value['pvp_rank_class_level'],
                $value['score']
            );
            $this->setUserCache($value['sys_pvp_season_id'], $value['my_id'], $value['score']);
        }
        // 抽選を実行
        $exec = $this->pvpMatchingService->getMatchingOpponentSelectStatusDatas($usrUserId, $sysPvpSeasonId, $now);

        // キャッシュがあればキャッシュからデータがとれていること 
        // 格下のキャッシュを作っていないのでダミーのIDが取れる
        $this->assertEquals('cacheUserId5', $exec[0]->getPvpUserProfile()->getMyId());
        $this->assertEquals('cacheUserId4', $exec[1]->getPvpUserProfile()->getMyId());
        $this->assertStringContainsString('dummyUserId', $exec[2]->getPvpUserProfile()->getMyId());
    }

    public function test_getMatchingOpponentSelectStatusDatas同格キャッシュなし(): void
    {
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();
        $sysPvpSeasonId = sprintf(
            '%04d0%02d',
            $now->isoWeekYear,
            $now->isoWeek
        );

        UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'score' => 25,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 3,
        ]);
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'is_valid' => PvpSessionStatus::STARTED,
        ]);

        $this->setDummyUser();
        $this->setPvpData();
        $this->setMstPvpMatchingScoreRange();

        foreach ($this->setUserCacheBaseParam4($sysPvpSeasonId) as $value) {
            $this->pvpCacheService->addOpponentCandidate(
                $value['sys_pvp_season_id'],
                $value['my_id'],
                $value['pvp_rank_class_type'],
                $value['pvp_rank_class_level'],
                $value['score']
            );
            $this->setUserCache($value['sys_pvp_season_id'], $value['my_id'], $value['score']);
        }
        // 抽選を実行
        $exec = $this->pvpMatchingService->getMatchingOpponentSelectStatusDatas($usrUserId, $sysPvpSeasonId, $now);

        // キャッシュがあればキャッシュからデータがとれていること 
        // 同格のキャッシュを作っていないのでダミーのIDが取れる
        $this->assertEquals('cacheUserId5', $exec[0]->getPvpUserProfile()->getMyId());
        $this->assertStringContainsString('dummyUserId', $exec[1]->getPvpUserProfile()->getMyId());
        $this->assertEquals('cacheUserId1', $exec[2]->getPvpUserProfile()->getMyId());
    }

    public function test_getMatchingOpponentSelectStatusDatas自身は選出されない(): void
    {
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();

        $sysPvpSeasonId = sprintf(
            '%04d0%02d',
            $now->isoWeekYear,
            $now->isoWeek
        );

        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'score' => 25,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 3,
        ]);
        $usrUserProfile = UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'is_valid' => PvpSessionStatus::STARTED,
        ]);

        $this->setDummyUser();
        $this->setPvpData();
        $this->setMstPvpMatchingScoreRange();

        // 同格無しでキャッシュを設定
        foreach ($this->setUserCacheBaseParam4($sysPvpSeasonId) as $value) {
            $this->pvpCacheService->addOpponentCandidate(
                $value['sys_pvp_season_id'],
                $value['my_id'],
                $value['pvp_rank_class_type'],
                $value['pvp_rank_class_level'],
                $value['score']
            );
            $this->setUserCache($value['sys_pvp_season_id'], $value['my_id'], $value['score']);
        }
        // 同格として自身をキャッシュに追加
        $this->pvpCacheService->addOpponentCandidate(
            $usrPvp->sys_pvp_season_id,
            $usrUserProfile->my_id,
            $usrPvp->pvp_rank_class_type,
            $usrPvp->pvp_rank_class_level,
            $usrPvp->score
        );
        $this->setUserCache(
            $usrPvp->sys_pvp_season_id,
            $usrUserProfile->my_id,
            $usrPvp->score
        );
        // 抽選を実行
        $exec = $this->pvpMatchingService->getMatchingOpponentSelectStatusDatas($usrUserId, $sysPvpSeasonId, $now);

        $this->assertEquals('cacheUserId5', $exec[0]->getPvpUserProfile()->getMyId());
        $this->assertStringContainsString('dummyUserId', $exec[1]->getPvpUserProfile()->getMyId());
        $this->assertEquals('cacheUserId1', $exec[2]->getPvpUserProfile()->getMyId());
    }

    public function test_getMatchingOpponentSelectStatusDatas自分以外なら選出される(): void
    {
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();

        $sysPvpSeasonId = sprintf(
            '%04d0%02d',
            $now->isoWeekYear,
            $now->isoWeek
        );

        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'score' => 25,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 3,
        ]);
        $usrUserProfile = UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'is_valid' => PvpSessionStatus::STARTED,
        ]);

        $this->setDummyUser();
        $this->setPvpData();
        $this->setMstPvpMatchingScoreRange();

        // 同格無しでキャッシュを設定
        foreach ($this->setUserCacheBaseParam4($sysPvpSeasonId) as $value) {
            $this->pvpCacheService->addOpponentCandidate(
                $value['sys_pvp_season_id'],
                $value['my_id'],
                $value['pvp_rank_class_type'],
                $value['pvp_rank_class_level'],
                $value['score']
            );
            $this->setUserCache($value['sys_pvp_season_id'], $value['my_id'], $value['score']);
        }
        // 同格として自身をキャッシュに追加
        $this->pvpCacheService->addOpponentCandidate(
            $usrPvp->sys_pvp_season_id,
            'testCacheUserId', // 自分以外の同格ユーザーID
            $usrPvp->pvp_rank_class_type,
            $usrPvp->pvp_rank_class_level,
            $usrPvp->score
        );
        $this->setUserCache(
            $usrPvp->sys_pvp_season_id,
            'testCacheUserId', // 自分以外の同格ユーザーID
            $usrPvp->score
        );
        // 抽選を実行
        $exec = $this->pvpMatchingService->getMatchingOpponentSelectStatusDatas($usrUserId, $sysPvpSeasonId, $now);

        $this->assertEquals('cacheUserId5', $exec[0]->getPvpUserProfile()->getMyId());
        $this->assertEquals('testCacheUserId', $exec[1]->getPvpUserProfile()->getMyId());
        $this->assertEquals('cacheUserId1', $exec[2]->getPvpUserProfile()->getMyId());
    }

    public function test_getDummyOpponentでMstOutpostIdが正しく設定されること(): void
    {
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();
        $sysPvpSeasonId = sprintf(
            '%04d0%02d',
            $now->isoWeekYear,
            $now->isoWeek
        );

        UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'score' => 25,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 3,
        ]);
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'is_valid' => PvpSessionStatus::STARTED,
        ]);

        $this->setDummyUser();
        $this->setPvpData();
        $this->setMstPvpMatchingScoreRange();

        // 抽選を実行（キャッシュがないのでダミーユーザーが選出される）
        $exec = $this->pvpMatchingService->getMatchingOpponentSelectStatusDatas($usrUserId, $sysPvpSeasonId, $now);

        // ダミーユーザーが返されることを確認
        $this->assertStringContainsString('dummyUserId', $exec[0]->getPvpUserProfile()->getMyId());
        
        // デバッグ: 返されたダミーユーザーIDを確認
        $dummyUserId = $exec[0]->getPvpUserProfile()->getMyId();
        
        // ダミーユーザーのアウトポストリストが正しく設定されていることを確認
        $outposts = $exec[0]->getUsrOutpostEnhancements();
        
        // デバッグ: アウトポスト数を確認
        $this->assertGreaterThan(0, $outposts->count(), "ダミーユーザー {$dummyUserId} のアウトポストが見つかりません");
        
        // 各アウトポストにmstOutpostIdが設定されていることを確認
        foreach ($outposts as $outpost) {
            $this->assertNotNull($outpost->getMstOutpostId());
            $this->assertIsString($outpost->getMstOutpostId());
            $this->assertTrue(in_array($outpost->getMstOutpostId(), ['outpost_1', 'outpost_2']));
        }
    }

    public function test_getMatchingOpponentSelectStatusDatasダミーは格下として判定されている(): void
    {
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();

        $sysPvpSeasonId = sprintf(
            '%04d0%02d',
            $now->isoWeekYear,
            $now->isoWeek
        );

        UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'score' => 25,
            'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
            'pvp_rank_class_level' => 3,
        ]);
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'is_valid' => PvpSessionStatus::STARTED,
        ]);

        $this->setDummyUser();
        $this->setPvpData();
        $this->setMstPvpMatchingScoreRange();

        foreach ($this->setUserCacheBaseParam2($sysPvpSeasonId) as $value) {
            $this->pvpCacheService->addOpponentCandidate(
                $value['sys_pvp_season_id'],
                $value['my_id'],
                $value['pvp_rank_class_type'],
                $value['pvp_rank_class_level'],
                $value['score']
            );
            $this->setUserCache($value['sys_pvp_season_id'], $value['my_id'], $value['score']);
        }
        // 抽選を実行
        $exec = $this->pvpMatchingService->getMatchingOpponentSelectStatusDatas($usrUserId, $sysPvpSeasonId, $now);

        // キャッシュがあればキャッシュからデータがとれていること 
        // 格上のキャッシュを作っていないのでダミーのIDが取れる
        $this->assertStringContainsString('dummyUserId', $exec[0]->getPvpUserProfile()->getMyId());
        $this->assertEquals('cacheUserId4', $exec[1]->getPvpUserProfile()->getMyId());
        $this->assertEquals('cacheUserId1', $exec[2]->getPvpUserProfile()->getMyId());

        // ダミーデータが格下として判定されていることを確認
        $this->assertEquals(PvpMatchingType::Lower, $exec[0]->getPvpUserProfile()->getMatchingType());
        $this->assertEquals(PvpMatchingType::Same,  $exec[1]->getPvpUserProfile()->getMatchingType());
        $this->assertEquals(PvpMatchingType::Lower, $exec[2]->getPvpUserProfile()->getMatchingType());

        // ダミーデータのWinAddPointが格下として判定されていることを確認
        // BRONZE帯で勝利 10 + (格上 3/同格 2/格下 1)
        $this->assertEquals(11, $exec[0]->getPvpUserProfile()->getWinAddPoint());
        $this->assertEquals(12,  $exec[1]->getPvpUserProfile()->getWinAddPoint());
        $this->assertEquals(11, $exec[2]->getPvpUserProfile()->getWinAddPoint());
    }

    private function setUserCache($sysPvpSeasonId, $myId, $score)
    {
        $pvpUnits = collect([
            new PvpUnitData('unit_001', 50, 5, 3),
            new PvpUnitData('unit_002', 45, 4, 2),
        ]);

        $opponentSelectStatusData = new OpponentSelectStatusData(
            $myId,
            'opponent_avatar',
            '1000',
            'opponent_123',
            $score,
             collect([]),
            100
        );

        $pvpEncyclopediaEffects = collect([
            new PvpEncyclopediaEffect('effect_001'),
            new PvpEncyclopediaEffect('effect_002'),
        ]);

        $opponentPvpStatusData = new OpponentPvpStatusData(
            $opponentSelectStatusData,
            $pvpUnits,
            collect([]),
            $pvpEncyclopediaEffects,
            collect(['artwork_001', 'artwork_002']),
            collect([])
        );
        $this->pvpCacheService->addOpponentStatus($sysPvpSeasonId,$myId,$opponentPvpStatusData);
    }

    // すべてキャッシュから返却されるパターン
    private function setUserCacheBaseParam($sysPvpSeasonId)
    {
        return [
            [
                'my_id' => 'cacheUserId1',
                'sys_pvp_season_id' => $sysPvpSeasonId,
                'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
                'pvp_rank_class_level' => 2,
                'score' => 17, // 格下戦範囲(15-19)に調整 - 唯一の格下戦候補
            ],
            [
                'my_id' => 'cacheUserId4',
                'sys_pvp_season_id' => $sysPvpSeasonId,
                'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
                'pvp_rank_class_level' => 3,
                'score' => 25, // 同格戦範囲(23-27)に調整 - 唯一の同格戦候補
            ],
            [
                'my_id' => 'cacheUserId5',
                'sys_pvp_season_id' => $sysPvpSeasonId,
                'pvp_rank_class_type' => PvpRankClassType::SILVER->value,
                'pvp_rank_class_level' => 1,
                'score' => 32, // 格上戦範囲(30-35)に調整 - 唯一の格上戦候補
            ],
        ];
    }

    // 格上にキャッシュを設定しない
    private function setUserCacheBaseParam2($sysPvpSeasonId)
    {
        return [
            [
                'my_id' => 'cacheUserId1',
                'sys_pvp_season_id' => $sysPvpSeasonId,
                'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
                'pvp_rank_class_level' => 2,
                'score' => 17, // 格下戦範囲(15-19)に調整 - 唯一の格下戦候補
            ],
            [
                'my_id' => 'cacheUserId4',
                'sys_pvp_season_id' => $sysPvpSeasonId,
                'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
                'pvp_rank_class_level' => 3,
                'score' => 25, // 同格戦範囲(23-27)に調整 - 唯一の同格戦候補
            ],
        ];
    }

    // 格下にキャッシュを設定しない
    private function setUserCacheBaseParam3($sysPvpSeasonId)
    {
        return [
            [
                'my_id' => 'cacheUserId4',
                'sys_pvp_season_id' => $sysPvpSeasonId,
                'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
                'pvp_rank_class_level' => 3,
                'score' => 25, // 同格戦範囲(23-27)に調整 - 唯一の同格戦候補
            ],
            [
                'my_id' => 'cacheUserId5',
                'sys_pvp_season_id' => $sysPvpSeasonId,
                'pvp_rank_class_type' => PvpRankClassType::SILVER->value,
                'pvp_rank_class_level' => 1,
                'score' => 32, // 格上戦範囲(30-35)に調整 - 唯一の格上戦候補
            ],
        ];
    }

    // 同格にキャッシュを設定しない
    private function setUserCacheBaseParam4($sysPvpSeasonId)
    {
        return [
            [
                'my_id' => 'cacheUserId1',
                'sys_pvp_season_id' => $sysPvpSeasonId,
                'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
                'pvp_rank_class_level' => 2,
                'score' => 17, // 格下戦範囲(15-19)に調整 - 唯一の格下戦候補
            ],
            [
                'my_id' => 'cacheUserId5',
                'sys_pvp_season_id' => $sysPvpSeasonId,
                'pvp_rank_class_type' => PvpRankClassType::SILVER->value,
                'pvp_rank_class_level' => 1,
                'score' => 32, // 格上戦範囲(30-35)に調整 - 唯一の格上戦候補
            ],
        ];
    }

    private function setDummyUser()
    {
        MstDummyUser::factory()->createMany([
            [
                'id' => 'dummyUserId1',
                'release_key' => 1,
                'mst_unit_id' => 'dummyUnitId1',
                'mst_emblem_id' => 'dummyUEmblemd1',
                'grade_unit_level_total_count'   => 1,
            ],
            [
                'id' => 'dummyUserId2',
                'release_key' => 1,
                'mst_unit_id' => 'dummyUnitId2',
                'mst_emblem_id' => 'dummyUEmblemd2',
                'grade_unit_level_total_count'   => 1,
            ],
            [
                'id' => 'dummyUserId3',
                'release_key' => 1,
                'mst_unit_id' => 'dummyUnitId3',
                'mst_emblem_id' => 'dummyUEmblemd3',
                'grade_unit_level_total_count'   => 1,
            ],
            [
                'id' => 'dummyUserId4',
                'release_key' => 1,
                'mst_unit_id' => 'dummyUnitId4',
                'mst_emblem_id' => 'dummyUEmblemd4',
                'grade_unit_level_total_count'   => 1,
            ],
            [
                'id' => 'dummyUserId5',
                'release_key' => 1,
                'mst_unit_id' => 'dummyUnitId5',
                'mst_emblem_id' => 'dummyUEmblemd5',
                'grade_unit_level_total_count'   => 1,
            ],
        ]);

        MstDummyUserI18n::factory()->createMany([
            [
                'id' => 'dummyUserId1',
                'mst_dummy_user_id' => 'dummyUserId1',
                'release_key'   => 1,
                'name' => 'ダミー1',
            ],
            [
                'id' => 'dummyUserId2',
                'mst_dummy_user_id' => 'dummyUserId2',
                'release_key'   => 1,
                'name' => 'ダミー2',
            ],
            [
                'id' => 'dummyUserId3',
                'mst_dummy_user_id' => 'dummyUserId3',
                'release_key'   => 1,
                'name' => 'ダミー3',
            ],
            [
                'id' => 'dummyUserId4',
                'mst_dummy_user_id' => 'dummyUserId4',
                'release_key'   => 1,
                'name' => 'ダミー4',
            ],
            [
                'id' => 'dummyUserId5',
                'mst_dummy_user_id' => 'dummyUserId5',
                'release_key'   => 1,
                'name' => 'ダミー5',
            ],
        ]);

        MstPvpDummy::factory()->createMany([
            // Upper用のダミーデータ
            [
                'id' => 'mstPvpId1',
                'release_key' => 1,
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => 3,
                'matching_type' => PvpMatchingType::Upper->value,
                'mst_dummy_user_id' => 'dummyUserId1',
            ],
            [
                'id' => 'mstPvpId2',
                'release_key' => 1,
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => 3,
                'matching_type' => PvpMatchingType::Upper->value,
                'mst_dummy_user_id' => 'dummyUserId4',
            ],
            // Same用のダミーデータ
            [
                'id' => 'mstPvpId3',
                'release_key' => 1,
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => 3,
                'matching_type' => PvpMatchingType::Same->value,
                'mst_dummy_user_id' => 'dummyUserId2',
            ],
            [
                'id' => 'mstPvpId4',
                'release_key' => 1,
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => 3,
                'matching_type' => PvpMatchingType::Same->value,
                'mst_dummy_user_id' => 'dummyUserId5',
            ],
            // Lower用のダミーデータ
            [
                'id' => 'mstPvpId5',
                'release_key' => 1,
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => 3,
                'matching_type' => PvpMatchingType::Lower->value,
                'mst_dummy_user_id' => 'dummyUserId3',
            ],
        ]);

        MstDummyUserUnit::factory()->createMany([
            [
                'id' => 'mstPvpId1',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId1',
                'mst_unit_id' =>  'dummyUnitId1',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId2',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId1',
                'mst_unit_id' =>  'dummyUnitId2',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId3',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId1',
                'mst_unit_id' =>  'dummyUnitId3',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId4',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId1',
                'mst_unit_id' =>  'dummyUnitId4',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId5',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId1',
                'mst_unit_id' =>  'dummyUnitId5',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId6',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId2',
                'mst_unit_id' =>  'dummyUnitId3',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId7',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId2',
                'mst_unit_id' =>  'dummyUnitId4',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId8',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId2',
                'mst_unit_id' =>  'dummyUnitId5',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],

            [
                'id' => 'mstPvpId9',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId3',
                'mst_unit_id' =>  'dummyUnitId3',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId10',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId3',
                'mst_unit_id' =>  'dummyUnitId4',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId11',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId3',
                'mst_unit_id' =>  'dummyUnitId5',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],

            [
                'id' => 'mstPvpId12',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId4',
                'mst_unit_id' =>  'dummyUnitId3',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId13',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId4',
                'mst_unit_id' =>  'dummyUnitId4',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId14',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId4',
                'mst_unit_id' =>  'dummyUnitId5',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            
            [
                'id' => 'mstPvpId15',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId5',
                'mst_unit_id' =>  'dummyUnitId3',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId16',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId5',
                'mst_unit_id' =>  'dummyUnitId4',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
            [
                'id' => 'mstPvpId17',
                'release_key' => 1,
                'mst_dummy_user_id' => 'dummyUserId5',
                'mst_unit_id' =>  'dummyUnitId5',
                'level' => 10,
                'rank' => 5,
                'grade_level' => 1,
            ],
        ]);
    }

    private function setPvpData()
    {
        MstPvp::factory()->createMany([
            [
                'id' => 'mstPvpId',
                'item_challenge_cost_amount' => 1,
            ],
            [
                'id' => 'default_pvp',
                'item_challenge_cost_amount' => 1,
            ],
        ]);

        MstPvpRank::factory()->createMany([
            [
                'id' => PvpRankClassType::BRONZE->value . '_1',
                'release_key' => '2025024',
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => 1,
                'required_lower_score' => 1,
            ],
            [
                'id' => PvpRankClassType::BRONZE->value . '_2',
                'release_key' => '2025024',
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => 2,
                'required_lower_score' => 10,
            ],
            [
                'id' => PvpRankClassType::BRONZE->value . '_3',
                'release_key' => '2025024',
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => 3,
                'required_lower_score' => 20,
            ],
            [
                'id' => PvpRankClassType::SILVER->value . '_1',
                'release_key' => '2025024',
                'rank_class_type' => PvpRankClassType::SILVER->value,
                'rank_class_level' => 1,
                'required_lower_score' => 30,
            ],
            [
                'id' => PvpRankClassType::SILVER->value . '_2',
                'release_key' => '2025024',
                'rank_class_type' => PvpRankClassType::SILVER->value,
                'rank_class_level' => 2,
                'required_lower_score' => 40,
            ],
            [
                'id' => PvpRankClassType::SILVER->value . '_3',
                'release_key' => '2025024',
                'rank_class_type' => PvpRankClassType::SILVER->value,
                'rank_class_level' => 3,
                'required_lower_score' => 50,
            ],
        ]);

        MstPvpBonusPoint::factory()->createMany([
            [
                'id' => 'bonus_point_1',
                'release_key' => 1,
                'condition_value' => PvpRankClassType::BRONZE->value,
                'bonus_point' => 1,
                'bonus_type' => PvpBonusType::WinLowerBonus->value,
            ],
            [
                'id' => 'bonus_point_2',
                'release_key' => 1,
                'condition_value' => PvpRankClassType::BRONZE->value,
                'bonus_point' => 2,
                'bonus_type' => PvpBonusType::WinSameBonus->value,
            ],
            [
                'id' => 'bonus_point_3',
                'release_key' => 1,
                'condition_value' => PvpRankClassType::BRONZE->value,
                'bonus_point' => 3,
                'bonus_type' => PvpBonusType::WinUpperBonus->value,
            ],
            [
                'id' => 'bonus_point_4',
                'release_key' => 1,
                'condition_value' => PvpRankClassType::SILVER->value,
                'bonus_point' => 4,
                'bonus_type' => PvpBonusType::WinLowerBonus->value,
            ],
            [
                'id' => 'bonus_point_5',
                'release_key' => 1,
                'condition_value' => PvpRankClassType::SILVER->value,
                'bonus_point' => 5,
                'bonus_type' => PvpBonusType::WinSameBonus->value,
            ],
            [
                'id' => 'bonus_point_6',
                'release_key' => 1,
                'condition_value' => PvpRankClassType::SILVER->value,
                'bonus_point' => 6,
                'bonus_type' => PvpBonusType::WinUpperBonus->value,
            ],
        ]);
    }

    private function setMstPvpMatchingScoreRange()
    {
        // MstOutpostEnhancementを作成
        MstOutpostEnhancement::factory()->createMany([
            [
                'id' => 'enhance_1_3',
                'mst_outpost_id' => 'outpost_1',
                'outpost_enhancement_type' => 'LeaderPointSpeed',
                'asset_key' => 'asset_1',
                'release_key' => 1,
            ],
            [
                'id' => 'enhance_2_3',
                'mst_outpost_id' => 'outpost_2',
                'outpost_enhancement_type' => 'OutpostHp',
                'asset_key' => 'asset_2',
                'release_key' => 1,
            ],
        ]);

        MstDummyOutpost::factory()->createMany([
            [
                'id' => 'dummyOutpostId1',
                'mst_dummy_user_id' => 'dummyUserId1',
                'mst_outpost_enhancement_id' => 'enhance_1_3',
                'level'   => 1,
            ],
            [
                'id' => 'dummyOutpostId2',
                'mst_dummy_user_id' => 'dummyUserId2',
                'mst_outpost_enhancement_id' => 'enhance_1_3',
                'level'   => 1,
            ],
            [
                'id' => 'dummyOutpostId3',
                'mst_dummy_user_id' => 'dummyUserId3',
                'mst_outpost_enhancement_id' => 'enhance_1_3',
                'level'   => 1,
            ],
            [
                'id' => 'dummyOutpostId4',
                'mst_dummy_user_id' => 'dummyUserId4',
                'mst_outpost_enhancement_id' => 'enhance_2_3',
                'level'   => 1,
            ],
            [
                'id' => 'dummyOutpostId5',
                'mst_dummy_user_id' => 'dummyUserId5',
                'mst_outpost_enhancement_id' => 'enhance_2_3',
                'level'   => 1,
            ],
        ]);

        MstPvpMatchingScoreRange::factory()->createMany([
            [
                'id' => 'Bronze_1',
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => '1',
                'upper_rank_max_score' => 10,
                'upper_rank_min_score' => 5,
                'same_rank_max_score' => 4,
                'same_rank_min_score' => 0,
                'lower_rank_max_score' => 0,
                'lower_rank_min_score' => 0,
                'release_key' => 1,
            ],
            [
                'id' => 'Bronze_2',
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => '2',
                'upper_rank_max_score' => 10,
                'upper_rank_min_score' => 5,
                'same_rank_max_score' => 2,
                'same_rank_min_score' => -2,
                'lower_rank_max_score' => -6,
                'lower_rank_min_score' => -10,
                'release_key' => 1,
            ],
            [
                'id' => 'Bronze_3',
                'rank_class_type' => PvpRankClassType::BRONZE->value,
                'rank_class_level' => '3',
                'upper_rank_max_score' => 10,
                'upper_rank_min_score' => 5,
                'same_rank_max_score' => 2,
                'same_rank_min_score' => -2,
                'lower_rank_max_score' => -6,
                'lower_rank_min_score' => -10,
                'release_key' => 1,
            ],
            [
                'id' => 'SILVER_1',
                'rank_class_type' => PvpRankClassType::SILVER->value,
                'rank_class_level' => '1',
                'upper_rank_max_score' => 10,
                'upper_rank_min_score' => 5,
                'same_rank_max_score' => 2,
                'same_rank_min_score' => -2,
                'lower_rank_max_score' => -6,
                'lower_rank_min_score' => -10,
                'release_key' => 1,
            ],
            [
                'id' => 'SILVER_2',
                'rank_class_type' => PvpRankClassType::SILVER->value,
                'rank_class_level' => '2',
                'upper_rank_max_score' => 10,
                'upper_rank_min_score' => 5,
                'same_rank_max_score' => 2,
                'same_rank_min_score' => -2,
                'lower_rank_max_score' => -6,
                'lower_rank_min_score' => -10,
                'release_key' => 1,
            ],
            [
                'id' => 'SILVER_3',
                'rank_class_type' => PvpRankClassType::SILVER->value,
                'rank_class_level' => '3',
                'upper_rank_max_score' => 10,
                'upper_rank_min_score' => 5,
                'same_rank_max_score' => 2,
                'same_rank_min_score' => -2,
                'lower_rank_max_score' => -6,
                'lower_rank_min_score' => -10,
                'release_key' => 1,
            ],
        ]);
    }
}
