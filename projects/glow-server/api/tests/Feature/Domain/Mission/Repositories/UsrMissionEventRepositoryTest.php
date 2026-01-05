<?php

namespace Tests\Feature\Domain\Mission;

use App\Domain\Mission\Entities\UsrMissionEventBundle;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Models\Eloquent\UsrMissionEvent;
use App\Domain\Mission\Repositories\UsrMissionEventRepository;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UsrMissionEventRepositoryTest extends TestCase
{
    private UsrMissionEventRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(UsrMissionEventRepository::class);
    }

    public function test_getByMstMissionIds_データ取得とキャッシュが正常にできる_Event()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        // 取得対象データを用意
        $recordBase = [
            'usr_user_id' => $usrUserId,
            'mission_type' => MissionType::EVENT->getIntValue(),
        ];
        UsrMissionEvent::factory()->createMany([
            ['mst_mission_id' => 'event1', ...$recordBase],
            ['mst_mission_id' => 'event2', ...$recordBase],
            ['mst_mission_id' => 'event3', ...$recordBase],
        ]);
        // 取得対象外のデータも用意しておく
        $recordBase = [
            'usr_user_id' => $usrUserId,
        ];
        UsrMissionEvent::factory()->createMany([
            ['mission_type' => MissionType::EVENT_DAILY->getIntValue(), 'mst_mission_id' => 'eventDaily1', ...$recordBase],
            // 取得対象データと同じidであっても取得されない
            ['mission_type' => MissionType::EVENT_DAILY->getIntValue(), 'mst_mission_id' => 'event1', ...$recordBase],
        ]);

        // 実行されるクエリを記録する
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->toRawSql();
        });

        // Exercise
        $result = $this->repository->getByMstMissionIds(
            $usrUserId,
            mstMissionEventIds: [
                'event1', 'event3', // DBにあるデータ
                'invalid', // DBにないデータ
                'event3', // 重複してもuniqueにしているので問題ない
            ],
        );

        // Verify
        $this->assertInstanceOf(UsrMissionEventBundle::class, $result);

        // 実行クエリの確認
        $this->assertCount(1, $queries);
        $this->assertEquals(
            "select * from `usr_mission_events` where `usr_user_id` = '$usrUserId' and ((`mission_type` = 11 and `mst_mission_id` in ('event1', 'event3', 'invalid')))",
            $queries[0]
        );

        // 取得内容の確認
        $actual = $result->getEvents();
        $this->assertCount(2, $actual);

        $this->assertArrayHasKey('event1', $actual);
        $this->assertEquals(MissionType::EVENT->getIntValue(), $actual['event1']->getMissionType());
        $this->assertArrayHasKey('event3', $actual);
        $this->assertEquals(MissionType::EVENT->getIntValue(), $actual['event3']->getMissionType());

        $this->assertCount(0, $result->getEventDailies());

        // ユーザーキャッシュの確認
        $actual = $this->getUsrModelManagerPrivateVariable('models');
        $this->assertCount(1, $actual);

        $this->assertArrayHasKey(UsrMissionEventRepository::class, $actual);
        $actual = $actual[UsrMissionEventRepository::class];
        $this->assertCount(2, $actual);

        $actual = collect(array_values($actual))->keyBy(fn($model) => $model->getMstMissionId());
        $this->assertCount(2, $actual);

        $this->assertArrayHasKey('event1', $actual);
        $this->assertEquals(MissionType::EVENT->getIntValue(), $actual['event1']->getMissionType());
        $this->assertArrayHasKey('event3', $actual);
        $this->assertEquals(MissionType::EVENT->getIntValue(), $actual['event3']->getMissionType());
    }

    public function test_getByMstMissionIds_データ取得とキャッシュが正常にできる_全ミッションタイプ指定()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        // 取得対象データを用意
        $recordBase = [
            'usr_user_id' => $usrUserId,
            'mission_type' => MissionType::EVENT->getIntValue(),
        ];
        UsrMissionEvent::factory()->createMany([
            ['mst_mission_id' => 'event1', ...$recordBase],
            ['mst_mission_id' => 'event2', ...$recordBase],
            ['mst_mission_id' => 'event3', ...$recordBase],
        ]);
        $recordBase = [
            'usr_user_id' => $usrUserId,
            'mission_type' => MissionType::EVENT_DAILY->getIntValue(),
        ];
        UsrMissionEvent::factory()->createMany([
            ['mst_mission_id' => 'eventDaily1', ...$recordBase],
            ['mst_mission_id' => 'eventDaily2', ...$recordBase],
            ['mst_mission_id' => 'eventDaily3', ...$recordBase],
        ]);

        // 実行されるクエリを記録する
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->toRawSql();
        });

        // Exercise
        $result = $this->repository->getByMstMissionIds(
            $usrUserId,
            mstMissionEventIds: ['event1', 'event2'],
            mstMissionEventDailyIds: ['eventDaily1', 'eventDaily2', 'eventDaily3'],
        );

        // Verify
        // 実行クエリの確認
        $this->assertCount(1, $queries);
        $this->assertEquals(
            "select * from `usr_mission_events` where `usr_user_id` = '$usrUserId' and ("
            . "(`mission_type` = 11 and `mst_mission_id` in ('event1', 'event2'))"
            . " or (`mission_type` = 12 and `mst_mission_id` in ('eventDaily1', 'eventDaily2', 'eventDaily3'))"
            . ")",
            $queries[0]
        );

        // 取得内容の確認
        $this->assertInstanceOf(UsrMissionEventBundle::class, $result);
        $this->assertCount(2, $result->getEvents());
        $this->assertCount(3, $result->getEventDailies());

        // event
        $actual = $result->getEvents();
        $this->assertCount(2, $actual);
        $this->assertArrayHasKey('event1', $actual);
        $this->assertEquals(MissionType::EVENT->getIntValue(), $actual['event1']->getMissionType());
        $this->assertArrayHasKey('event2', $actual);
        $this->assertEquals(MissionType::EVENT->getIntValue(), $actual['event2']->getMissionType());

        // eventDaily
        $actual = $result->getEventDailies();
        $this->assertCount(3, $actual);
        $this->assertArrayHasKey('eventDaily1', $actual);
        $this->assertEquals(MissionType::EVENT_DAILY->getIntValue(), $actual['eventDaily1']->getMissionType());
        $this->assertArrayHasKey('eventDaily2', $actual);
        $this->assertEquals(MissionType::EVENT_DAILY->getIntValue(), $actual['eventDaily2']->getMissionType());
        $this->assertArrayHasKey('eventDaily3', $actual);
        $this->assertEquals(MissionType::EVENT_DAILY->getIntValue(), $actual['eventDaily3']->getMissionType());

        // ユーザーキャッシュの確認
        $actual = $this->getUsrModelManagerPrivateVariable('models');
        $this->assertCount(1, $actual);

        $this->assertArrayHasKey(UsrMissionEventRepository::class, $actual);
        $actual = $actual[UsrMissionEventRepository::class];
        $this->assertCount(5, $actual);

        $actual = collect(array_values($actual))->keyBy(function ($model) {
            return $model->getMissionType() . '_' . $model->getMstMissionId();
        });
        $this->assertCount(5, $actual);

        // // achievement
        // $this->assertArrayHasKey('1_achievement1', $actual);
        // $this->assertEquals(MissionType::ACHIEVEMENT->getIntValue(), $actual['1_achievement1']->getMissionType());
        // $this->assertArrayHasKey('1_achievement2', $actual);
        // $this->assertEquals(MissionType::ACHIEVEMENT->getIntValue(), $actual['1_achievement2']->getMissionType());

        // event
        $this->assertArrayHasKey('11_event1', $actual);
        $this->assertEquals(MissionType::EVENT->getIntValue(), $actual['11_event1']->getMissionType());
        $this->assertArrayHasKey('11_event2', $actual);
        $this->assertEquals(MissionType::EVENT->getIntValue(), $actual['11_event2']->getMissionType());

        // eventDaily
        $this->assertArrayHasKey('12_eventDaily1', $actual);
        $this->assertEquals(MissionType::EVENT_DAILY->getIntValue(), $actual['12_eventDaily1']->getMissionType());
        $this->assertArrayHasKey('12_eventDaily2', $actual);
        $this->assertEquals(MissionType::EVENT_DAILY->getIntValue(), $actual['12_eventDaily2']->getMissionType());
        $this->assertArrayHasKey('12_eventDaily3', $actual);
        $this->assertEquals(MissionType::EVENT_DAILY->getIntValue(), $actual['12_eventDaily3']->getMissionType());
    }

    public function test_getByMstMissionIds_クエリ実行せずにキャッシュから取得できる_Event()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        $recordBase = [
            'usr_user_id' => $usrUserId,
            'mission_type' => MissionType::EVENT->getIntValue(),
        ];
        UsrMissionEvent::factory()->createMany([
            ['mst_mission_id' => 'event1', ...$recordBase],
            ['mst_mission_id' => 'event2', ...$recordBase],
            ['mst_mission_id' => 'event3', ...$recordBase],
        ]);

        // 1回目：クエリを実行してキャッシュに詰める
        $this->repository->getByMstMissionIds(
            $usrUserId,
            mstMissionEventIds: ['event1', 'event3'],
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
            mstMissionEventIds: ['event1'],
        );

        // Verify
        $this->assertInstanceOf(UsrMissionEventBundle::class, $result);

        // 2回目にクエリが実行されていないことを確認
        $this->assertCount(0, $queries);

        // 取得内容の確認
        $actual = $result->getEvents();
        $this->assertCount(1, $actual);

        $this->assertCount(0, $result->getEventDailies());

        // ユーザーキャッシュの確認
        $actual = $this->getUsrModelManagerPrivateVariable('models');
        $this->assertCount(1, $actual);

        $this->assertArrayHasKey(UsrMissionEventRepository::class, $actual);
        $actual = $actual[UsrMissionEventRepository::class];
        $this->assertCount(2, $actual);
    }

    public function test_getByMstMissionIds_id配列が空ならクエリ実行せずに終了()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        $recordBase = [
            'usr_user_id' => $usrUserId,
            'mission_type' => MissionType::EVENT->getIntValue(),
        ];
        UsrMissionEvent::factory()->createMany([
            ['mst_mission_id' => 'event1', ...$recordBase],
            ['mst_mission_id' => 'event2', ...$recordBase],
            ['mst_mission_id' => 'event3', ...$recordBase],
        ]);

        // 実行されるクエリを記録する
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->toRawSql();
        });

        // Exercise
        $result = $this->repository->getByMstMissionIds(
            $usrUserId,
            mstMissionEventIds: [],
            mstMissionEventDailyIds: [],
        );

        // Verify
        $this->assertInstanceOf(UsrMissionEventBundle::class, $result);

        // クエリが実行されていないことを確認
        $this->assertCount(0, $queries);

        // 取得内容の確認
        $this->assertCount(0, $result->getEvents());
        $this->assertCount(0, $result->getEventDailies());

        // ユーザーキャッシュの確認
        $actual = $this->getUsrModelManagerPrivateVariable('models');
        $this->assertCount(0, $actual);
    }
}
