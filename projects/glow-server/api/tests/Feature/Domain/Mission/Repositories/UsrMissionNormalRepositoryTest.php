<?php

namespace Tests\Feature\Domain\Mission;

use App\Domain\Mission\Entities\UsrMissionNormalBundle;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Models\Eloquent\UsrMissionNormal;
use App\Domain\Mission\Repositories\UsrMissionNormalRepository;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UsrMissionNormalRepositoryTest extends TestCase
{
    private UsrMissionNormalRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(UsrMissionNormalRepository::class);
    }

    public function test_getByMstMissionIds_データ取得とキャッシュが正常にできる_Achievement()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        // 取得対象データを用意
        $recordBase = [
            'usr_user_id' => $usrUserId,
            'mission_type' => MissionType::ACHIEVEMENT->getIntValue(),
        ];
        UsrMissionNormal::factory()->createMany([
            ['mst_mission_id' => 'achievement1', ...$recordBase],
            ['mst_mission_id' => 'achievement2', ...$recordBase],
            ['mst_mission_id' => 'achievement3', ...$recordBase],
        ]);
        // 取得対象外のデータも用意しておく
        $recordBase = [
            'usr_user_id' => $usrUserId,
        ];
        UsrMissionNormal::factory()->createMany([
            ['mission_type' => MissionType::DAILY->getIntValue(), 'mst_mission_id' => 'daily1', ...$recordBase],
            ['mission_type' => MissionType::WEEKLY->getIntValue(), 'mst_mission_id' => 'weekly1', ...$recordBase],
            ['mission_type' => MissionType::BEGINNER->getIntValue(), 'mst_mission_id' => 'beginner1', ...$recordBase],
            // 取得対象データと同じidであっても取得されない
            ['mission_type' => MissionType::DAILY->getIntValue(), 'mst_mission_id' => 'achievement1', ...$recordBase],
            ['mission_type' => MissionType::WEEKLY->getIntValue(), 'mst_mission_id' => 'achievement2', ...$recordBase],
            ['mission_type' => MissionType::BEGINNER->getIntValue(), 'mst_mission_id' => 'achievement3', ...$recordBase],
        ]);

        // 実行されるクエリを記録する
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->toRawSql();
        });

        // Exercise
        $result = $this->repository->getByMstMissionIds(
            $usrUserId,
            mstMissionAchievementIds: [
                'achievement1', 'achievement3', // DBにあるデータ
                'invalid', // DBにないデータ
                'achievement3', // 重複してもuniqueにしているので問題ない
            ],
        );

        // Verify
        $this->assertInstanceOf(UsrMissionNormalBundle::class, $result);

        // 実行クエリの確認
        $this->assertCount(1, $queries);
        $this->assertEquals(
            "select * from `usr_mission_normals` where `usr_user_id` = '$usrUserId' and ((`mission_type` = 1 and `mst_mission_id` in ('achievement1', 'achievement3', 'invalid')))",
            $queries[0]
        );

        // 取得内容の確認
        $actual = $result->getAchievements();
        $this->assertCount(2, $actual);

        $this->assertArrayHasKey('achievement1', $actual);
        $this->assertEquals(MissionType::ACHIEVEMENT->getIntValue(), $actual['achievement1']->getMissionType());
        $this->assertArrayHasKey('achievement3', $actual);
        $this->assertEquals(MissionType::ACHIEVEMENT->getIntValue(), $actual['achievement3']->getMissionType());

        $this->assertCount(0, $result->getDailies());
        $this->assertCount(0, $result->getWeeklies());
        $this->assertCount(0, $result->getBeginners());

        // ユーザーキャッシュの確認
        $actual = $this->getUsrModelManagerPrivateVariable('models');
        $this->assertCount(1, $actual);

        $this->assertArrayHasKey(UsrMissionNormalRepository::class, $actual);
        $actual = $actual[UsrMissionNormalRepository::class];
        $this->assertCount(2, $actual);

        $actual = collect(array_values($actual))->keyBy(fn($model) => $model->getMstMissionId());
        $this->assertCount(2, $actual);

        $this->assertArrayHasKey('achievement1', $actual);
        $this->assertEquals(MissionType::ACHIEVEMENT->getIntValue(), $actual['achievement1']->getMissionType());
        $this->assertArrayHasKey('achievement3', $actual);
        $this->assertEquals(MissionType::ACHIEVEMENT->getIntValue(), $actual['achievement3']->getMissionType());
    }

    public function test_getByMstMissionIds_データ取得とキャッシュが正常にできる_Beginner()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        // 取得対象データを用意
        $recordBase = [
            'usr_user_id' => $usrUserId,
            'mission_type' => MissionType::BEGINNER->getIntValue(),
        ];
        UsrMissionNormal::factory()->createMany([
            ['mst_mission_id' => 'beginner1', ...$recordBase],
            ['mst_mission_id' => 'beginner2', ...$recordBase],
            ['mst_mission_id' => 'beginner3', ...$recordBase],
        ]);
        // 取得対象外のデータも用意しておく
        $recordBase = [
            'usr_user_id' => $usrUserId,
        ];
        UsrMissionNormal::factory()->createMany([
            ['mission_type' => MissionType::DAILY->getIntValue(), 'mst_mission_id' => 'daily1', ...$recordBase],
            ['mission_type' => MissionType::WEEKLY->getIntValue(), 'mst_mission_id' => 'weekly1', ...$recordBase],
            ['mission_type' => MissionType::ACHIEVEMENT->getIntValue(), 'mst_mission_id' => 'achievement1', ...$recordBase],
            // 取得対象データと同じidであっても取得されない
            ['mission_type' => MissionType::DAILY->getIntValue(), 'mst_mission_id' => 'beginner1', ...$recordBase],
            ['mission_type' => MissionType::WEEKLY->getIntValue(), 'mst_mission_id' => 'beginner2', ...$recordBase],
            ['mission_type' => MissionType::ACHIEVEMENT->getIntValue(), 'mst_mission_id' => 'beginner3', ...$recordBase],
        ]);

        // 実行されるクエリを記録する
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->toRawSql();
        });

        // Exercise
        $result = $this->repository->getByMstMissionIds(
            $usrUserId,
            mstMissionBeginnerIds: [
                'beginner2', 'beginner3', // DBにあるデータ
                'invalid', // DBにないデータ
                'beginner2', // 重複してもuniqueにしているので問題ない
            ],
        );

        // Verify
        $this->assertInstanceOf(UsrMissionNormalBundle::class, $result);

        // 実行クエリの確認
        $this->assertCount(1, $queries);
        $this->assertEquals(
            "select * from `usr_mission_normals` where `usr_user_id` = '$usrUserId' and ((`mission_type` = 2 and `mst_mission_id` in ('beginner2', 'beginner3', 'invalid')))",
            $queries[0]
        );

        // 取得内容の確認
        $actual = $result->getBeginners();
        $this->assertCount(2, $actual);

        $this->assertArrayHasKey('beginner2', $actual);
        $this->assertEquals(MissionType::BEGINNER->getIntValue(), $actual['beginner2']->getMissionType());
        $this->assertArrayHasKey('beginner3', $actual);
        $this->assertEquals(MissionType::BEGINNER->getIntValue(), $actual['beginner3']->getMissionType());

        $this->assertCount(0, $result->getDailies());
        $this->assertCount(0, $result->getWeeklies());
        $this->assertCount(0, $result->getAchievements());

        // ユーザーキャッシュの確認
        $actual = $this->getUsrModelManagerPrivateVariable('models');
        $this->assertCount(1, $actual);

        $this->assertArrayHasKey(UsrMissionNormalRepository::class, $actual);
        $actual = $actual[UsrMissionNormalRepository::class];
        $this->assertCount(2, $actual);

        $actual = collect(array_values($actual))->keyBy(fn($model) => $model->getMstMissionId());
        $this->assertCount(2, $actual);

        $this->assertArrayHasKey('beginner2', $actual);
        $this->assertEquals(MissionType::BEGINNER->getIntValue(), $actual['beginner2']->getMissionType());
        $this->assertArrayHasKey('beginner3', $actual);
        $this->assertEquals(MissionType::BEGINNER->getIntValue(), $actual['beginner3']->getMissionType());
    }

    public function test_getByMstMissionIds_データ取得とキャッシュが正常にできる_全ミッションタイプ指定()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        // 取得対象データを用意
        $recordBase = [
            'usr_user_id' => $usrUserId,
            'mission_type' => MissionType::ACHIEVEMENT->getIntValue(),
        ];
        UsrMissionNormal::factory()->createMany([
            ['mst_mission_id' => 'achievement1', ...$recordBase],
            ['mst_mission_id' => 'achievement2', ...$recordBase],
            ['mst_mission_id' => 'achievement3', ...$recordBase],
        ]);
        $recordBase = [
            'usr_user_id' => $usrUserId,
            'mission_type' => MissionType::BEGINNER->getIntValue(),
        ];
        UsrMissionNormal::factory()->createMany([
            ['mst_mission_id' => 'beginner1', ...$recordBase],
            ['mst_mission_id' => 'beginner2', ...$recordBase],
            ['mst_mission_id' => 'beginner3', ...$recordBase],
        ]);
        $recordBase = [
            'usr_user_id' => $usrUserId,
            'mission_type' => MissionType::DAILY->getIntValue(),
        ];
        UsrMissionNormal::factory()->createMany([
            ['mst_mission_id' => 'daily1', ...$recordBase],
            ['mst_mission_id' => 'daily2', ...$recordBase],
            ['mst_mission_id' => 'daily3', ...$recordBase],
        ]);
        $recordBase = [
            'usr_user_id' => $usrUserId,
            'mission_type' => MissionType::WEEKLY->getIntValue(),
        ];
        UsrMissionNormal::factory()->createMany([
            ['mst_mission_id' => 'weekly1', ...$recordBase],
            ['mst_mission_id' => 'weekly2', ...$recordBase],
            ['mst_mission_id' => 'weekly3', ...$recordBase],
        ]);

        // 実行されるクエリを記録する
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->toRawSql();
        });

        // Exercise
        $result = $this->repository->getByMstMissionIds(
            $usrUserId,
            mstMissionAchievementIds: ['achievement1', 'achievement2'],
            mstMissionBeginnerIds: ['beginner2'],
            mstMissionDailyIds: ['daily1', 'daily3'],
            mstMissionWeeklyIds: ['weekly3', 'weekly1', 'weekly2'],
        );

        // Verify
        // 実行クエリの確認
        $this->assertCount(1, $queries);
        $this->assertEquals(
            "select * from `usr_mission_normals` where `usr_user_id` = '$usrUserId' and ("
            . "(`mission_type` = 1 and `mst_mission_id` in ('achievement1', 'achievement2'))"
            . " or (`mission_type` = 2 and `mst_mission_id` in ('beginner2'))"
            . " or (`mission_type` = 3 and `mst_mission_id` in ('daily1', 'daily3'))"
            . " or (`mission_type` = 5 and `mst_mission_id` in ('weekly3', 'weekly1', 'weekly2'))"
            . ")",
            $queries[0]
        );

        // 取得内容の確認
        $this->assertInstanceOf(UsrMissionNormalBundle::class, $result);
        $this->assertCount(2, $result->getAchievements());
        $this->assertCount(1, $result->getBeginners());
        $this->assertCount(2, $result->getDailies());
        $this->assertCount(3, $result->getWeeklies());

        // achievement
        $actual = $result->getAchievements();
        $this->assertArrayHasKey('achievement1', $actual);
        $this->assertEquals(MissionType::ACHIEVEMENT->getIntValue(), $actual['achievement1']->getMissionType());
        $this->assertArrayHasKey('achievement2', $actual);
        $this->assertEquals(MissionType::ACHIEVEMENT->getIntValue(), $actual['achievement2']->getMissionType());

        // beginner
        $actual = $result->getBeginners();
        $this->assertArrayHasKey('beginner2', $actual);
        $this->assertEquals(MissionType::BEGINNER->getIntValue(), $actual['beginner2']->getMissionType());

        // daily
        $actual = $result->getDailies();
        $this->assertArrayHasKey('daily1', $actual);
        $this->assertEquals(MissionType::DAILY->getIntValue(), $actual['daily1']->getMissionType());
        $this->assertArrayHasKey('daily3', $actual);
        $this->assertEquals(MissionType::DAILY->getIntValue(), $actual['daily3']->getMissionType());

        // weekly
        $actual = $result->getWeeklies();
        $this->assertArrayHasKey('weekly1', $actual);
        $this->assertEquals(MissionType::WEEKLY->getIntValue(), $actual['weekly1']->getMissionType());
        $this->assertArrayHasKey('weekly2', $actual);
        $this->assertEquals(MissionType::WEEKLY->getIntValue(), $actual['weekly2']->getMissionType());
        $this->assertArrayHasKey('weekly3', $actual);
        $this->assertEquals(MissionType::WEEKLY->getIntValue(), $actual['weekly3']->getMissionType());

        // ユーザーキャッシュの確認
        $actual = $this->getUsrModelManagerPrivateVariable('models');
        $this->assertCount(1, $actual);

        $this->assertArrayHasKey(UsrMissionNormalRepository::class, $actual);
        $actual = $actual[UsrMissionNormalRepository::class];
        $this->assertCount(8, $actual);

        $actual = collect(array_values($actual))->keyBy(function ($model) {
            return $model->getMissionType() . '_' . $model->getMstMissionId();
        });
        $this->assertCount(8, $actual);

        // achievement
        $this->assertArrayHasKey('1_achievement1', $actual);
        $this->assertEquals(MissionType::ACHIEVEMENT->getIntValue(), $actual['1_achievement1']->getMissionType());
        $this->assertArrayHasKey('1_achievement2', $actual);
        $this->assertEquals(MissionType::ACHIEVEMENT->getIntValue(), $actual['1_achievement2']->getMissionType());

        // beginner
        $this->assertArrayHasKey('2_beginner2', $actual);
        $this->assertEquals(MissionType::BEGINNER->getIntValue(), $actual['2_beginner2']->getMissionType());

        // daily
        $this->assertArrayHasKey('3_daily1', $actual);
        $this->assertEquals(MissionType::DAILY->getIntValue(), $actual['3_daily1']->getMissionType());
        $this->assertArrayHasKey('3_daily3', $actual);
        $this->assertEquals(MissionType::DAILY->getIntValue(), $actual['3_daily3']->getMissionType());

        // weekly
        $this->assertArrayHasKey('5_weekly1', $actual);
        $this->assertEquals(MissionType::WEEKLY->getIntValue(), $actual['5_weekly1']->getMissionType());
        $this->assertArrayHasKey('5_weekly2', $actual);
        $this->assertEquals(MissionType::WEEKLY->getIntValue(), $actual['5_weekly2']->getMissionType());
        $this->assertArrayHasKey('5_weekly3', $actual);
        $this->assertEquals(MissionType::WEEKLY->getIntValue(), $actual['5_weekly3']->getMissionType());
    }

    public function test_getByMstMissionIds_クエリ実行せずにキャッシュから取得できる_Achievement()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        $recordBase = [
            'usr_user_id' => $usrUserId,
            'mission_type' => MissionType::ACHIEVEMENT->getIntValue(),
        ];
        UsrMissionNormal::factory()->createMany([
            ['mst_mission_id' => 'achievement1', ...$recordBase],
            ['mst_mission_id' => 'achievement2', ...$recordBase],
            ['mst_mission_id' => 'achievement3', ...$recordBase],
        ]);

        // 1回目：クエリを実行してキャッシュに詰める
        $this->repository->getByMstMissionIds(
            $usrUserId,
            mstMissionAchievementIds: ['achievement1', 'achievement3'],
        );

        // 実行されるクエリを記録する
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->toRawSql();
        });

        // Exercise
        // 2回目：キャッシュにあるデータなので、クエリ実行せずに、キャッシュから取得する
        $result = $this->repository->getByMstMissionIds(
            $usrUserId,
            mstMissionAchievementIds: ['achievement1'],
        );

        // Verify
        $this->assertInstanceOf(UsrMissionNormalBundle::class, $result);

        // 2回目にクエリが実行されていないことを確認
        $this->assertCount(0, $queries);

        // 取得内容の確認
        $actual = $result->getAchievements();
        $this->assertCount(1, $actual);

        $this->assertCount(0, $result->getDailies());
        $this->assertCount(0, $result->getWeeklies());
        $this->assertCount(0, $result->getBeginners());

        // ユーザーキャッシュの確認
        $actual = $this->getUsrModelManagerPrivateVariable('models');
        $this->assertCount(1, $actual);

        $this->assertArrayHasKey(UsrMissionNormalRepository::class, $actual);
        $actual = $actual[UsrMissionNormalRepository::class];
        $this->assertCount(2, $actual);
    }

    public function test_getByMstMissionIds_id配列が空ならクエリ実行せずに終了()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        $recordBase = [
            'usr_user_id' => $usrUserId,
            'mission_type' => MissionType::ACHIEVEMENT->getIntValue(),
        ];
        UsrMissionNormal::factory()->createMany([
            ['mst_mission_id' => 'achievement1', ...$recordBase],
            ['mst_mission_id' => 'achievement2', ...$recordBase],
            ['mst_mission_id' => 'achievement3', ...$recordBase],
        ]);

        // 実行されるクエリを記録する
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->toRawSql();
        });

        // Exercise
        $result = $this->repository->getByMstMissionIds(
            $usrUserId,
            mstMissionAchievementIds: [],
            mstMissionBeginnerIds: [],
            mstMissionDailyIds: [],
            mstMissionWeeklyIds: [],
        );

        // Verify
        $this->assertInstanceOf(UsrMissionNormalBundle::class, $result);

        // クエリが実行されていないことを確認
        $this->assertCount(0, $queries);

        // 取得内容の確認
        $this->assertCount(0, $result->getAchievements());
        $this->assertCount(0, $result->getDailies());
        $this->assertCount(0, $result->getWeeklies());
        $this->assertCount(0, $result->getBeginners());

        // ユーザーキャッシュの確認
        $actual = $this->getUsrModelManagerPrivateVariable('models');
        $this->assertCount(0, $actual);
    }
}
