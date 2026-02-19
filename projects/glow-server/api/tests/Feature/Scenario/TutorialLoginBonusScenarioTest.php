<?php

namespace Tests\Feature\Scenario;

use App\Domain\Mission\Enums\MissionDailyBonusType;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Models\UsrMissionDailyBonus;
use App\Domain\Mission\Models\UsrMissionEventDailyBonus;
use App\Domain\Resource\Mst\Models\MstIdleIncentive;
use App\Domain\Resource\Mst\Models\MstMissionDailyBonus;
use App\Domain\Resource\Mst\Models\MstMissionEventDailyBonus;
use App\Domain\Resource\Mst\Models\MstMissionEventDailyBonusSchedule;
use App\Domain\Resource\Mst\Models\MstMissionReward;
use App\Domain\Resource\Mst\Models\MstTutorial;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Tutorial\Enums\TutorialFunctionName;
use App\Domain\Tutorial\Enums\TutorialType;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserLogin;
use App\Exceptions\HttpStatusCode;
use Illuminate\Support\Str;
use Tests\Feature\Http\Controllers\BaseControllerTestCase;
use Tests\Support\Traits\TestMultipleApiRequestsTrait;

class TutorialLoginBonusScenarioTest extends BaseControllerTestCase
{
    use TestMultipleApiRequestsTrait;

    protected string $baseUrl = '/api/';

    public function setUp(): void
    {
        parent::setUp();
        $this->createMasterRelease();
    }

    /**
     * 新規アカウント登録してログインしてチュートリアルのメインパートを完了した時点で、
     * 初めてログインボーナスを配布し、受け取ることができることを確認する
     */
    public function test_新規アカウント登録からチュートリアル完了後の初回ログインボーナス配布()
    {
        // Setup
        $this->fixTime('2024-04-01 15:00:00');

        // マスターデータ作成
        MstMissionDailyBonus::factory()->createMany([
            [
                'id' => 'dailyBonus_1',
                'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS,
                'login_day_count' => 1,
                'mst_mission_reward_group_id' => 'dailyBonusRewardGroup_1',
            ],
            [
                'id' => 'dailyBonus_2',
                'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS,
                'login_day_count' => 2,
                'mst_mission_reward_group_id' => 'dailyBonusRewardGroup_2',
            ],
        ]);

        // イベントデイリーボーナスのマスターデータ作成
        $eventSchedule = MstMissionEventDailyBonusSchedule::factory()->create([
            'id' => 'eventSchedule_1',
            'start_at' => '2024-04-01 00:00:00',
            'end_at' => '2024-04-30 23:59:59',
        ]);

        MstMissionEventDailyBonus::factory()->createMany([
            [
                'id' => 'eventDailyBonus_1',
                'mst_mission_event_daily_bonus_schedule_id' => $eventSchedule->id,
                'login_day_count' => 1,
                'mst_mission_reward_group_id' => 'eventDailyBonusRewardGroup_1',
            ],
            [
                'id' => 'eventDailyBonus_2',
                'mst_mission_event_daily_bonus_schedule_id' => $eventSchedule->id,
                'login_day_count' => 2,
                'mst_mission_reward_group_id' => 'eventDailyBonusRewardGroup_2',
            ],
        ]);

        MstUserLevel::factory()->create([
            'level' => 1,
            'stamina' => 10,
            'exp' => 0,
        ]);

        MstIdleIncentive::factory()->create();

        // 報酬グループとミッション報酬のマスターデータ作成
        MstMissionReward::factory()->createMany([
            [
                'id' => 'dailyBonusReward_1_1',
                'group_id' => 'dailyBonusRewardGroup_1',
                'resource_type' => 'coin',
                'resource_id' => '',
                'resource_amount' => 100,
                'sort_order' => 1,
            ],
            [
                'id' => 'dailyBonusReward_2_1',
                'group_id' => 'dailyBonusRewardGroup_2',
                'resource_type' => 'coin',
                'resource_id' => '',
                'resource_amount' => 200,
                'sort_order' => 1,
            ],
            [
                'id' => 'eventDailyBonusReward_1_1',
                'group_id' => 'eventDailyBonusRewardGroup_1',
                'resource_type' => 'coin',
                'resource_id' => '',
                'resource_amount' => 300,
                'sort_order' => 1,
            ],
            [
                'id' => 'eventDailyBonusReward_2_1',
                'group_id' => 'eventDailyBonusRewardGroup_2',
                'resource_type' => 'coin',
                'resource_id' => '',
                'resource_amount' => 400,
                'sort_order' => 1,
            ],
        ]);

        // チュートリアルマスターデータ作成
        MstTutorial::factory()->createMany([
            [
                'type' => TutorialType::INTRO,
                'sort_order' => 1,
                'function_name' => 'Tutorial1',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-31 23:59:59',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 2,
                'function_name' => 'Tutorial2',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-31 23:59:59',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 3,
                'function_name' => TutorialFunctionName::MAIN_PART_COMPLETED,
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-31 23:59:59',
            ],
        ]);

        // Exercise & Verify

        // 1. 新規アカウント登録 (sign_up)
        $signUpResponse = $this->postJson($this->baseUrl . 'sign_up', ['clientUuid' => Str::orderedUuid()->toString()]);
        $signUpResponse->assertStatus(HttpStatusCode::SUCCESS);
        $this->assertArrayHasKey('id_token', $signUpResponse->json());
        $idToken = $signUpResponse->json()['id_token'];

        // ユーザーIDを取得
        $usrUser = UsrUser::all()->first();
        $this->assertNotNull($usrUser);
        $usrUserId = $usrUser->getId();
        $this->setUsrUserId($usrUserId);

        $this->resetAppForNextRequest($usrUserId); // APIリクエスト後にインスタンスをクリア

        // 2. サインイン (sign_in) - 1分後
        $this->fixTime('2024-04-01 15:01:00');
        $signInResponse = $this->postJson($this->baseUrl . 'sign_in', ['id_token' => $idToken]);
        $signInResponse->assertStatus(HttpStatusCode::SUCCESS);
        $this->assertArrayHasKey('access_token', $signInResponse->json());
        $this->resetAppForNextRequest($usrUserId); // APIリクエスト後にインスタンスをクリア

        // 3. チュートリアル未完了時のゲーム更新・取得 (game/update_and_fetch) - 2分後
        // この時点ではまだチュートリアル未完了なので、ログインカウントは増えない
        $this->fixTime('2024-04-01 15:02:00');
        $gameUpdateResponse = $this->sendRequest('game/update_and_fetch');
        $gameUpdateResponse->assertStatus(HttpStatusCode::SUCCESS);
        $this->resetAppForNextRequest($usrUserId); // APIリクエスト後にインスタンスをクリア

        // ログインカウントが増えていないことを確認（レコードは作成されているが、カウントが0）
        $usrUserLogin = UsrUserLogin::where('usr_user_id', $usrUserId)->first();
        $this->assertNotNull($usrUserLogin, 'UsrUserLoginレコードは作成される');
        $this->assertEquals(0, $usrUserLogin->getLoginCount(), 'チュートリアル未完了時はログインカウントが0');
        $this->assertEquals(0, $usrUserLogin->getLoginDayCount(), 'チュートリアル未完了時はログイン日数が0');
        $this->assertEquals(0, $usrUserLogin->getLoginContinueDayCount(), 'チュートリアル未完了時は連続ログイン日数が0');
        $this->assertEquals(0, $usrUserLogin->getComebackDayCount(), 'チュートリアル未完了時は復帰日数が0');
        $this->assertNull($usrUserLogin->getFirstLoginAt(), 'チュートリアル未完了時は初回ログイン日時がnull');
        $this->assertNull($usrUserLogin->getLastLoginAt(), 'チュートリアル未完了時は最終ログイン日時がnull');
        $this->assertNotNull($usrUserLogin->getHourlyAccessedAt(), 'hourly_accessed_atは設定される');

         // レスポンスのデイリーボーナス、イベントデイリーボーナス報酬は空
        $fetchOtherResponse = $gameUpdateResponse->json()['fetchOther'];
        $this->assertArrayHasKey('dailyBonusRewards', $fetchOtherResponse);
        $this->assertEmpty($fetchOtherResponse['dailyBonusRewards'], 'チュートリアル完了時に配布するので空');
        $this->assertArrayHasKey('eventDailyBonusRewards', $fetchOtherResponse);
        $this->assertEmpty($fetchOtherResponse['eventDailyBonusRewards'], 'チュートリアル完了時に配布するので空');

        // デイリーボーナスが配布されていないことを確認
        $usrMissionDailyBonus = UsrMissionDailyBonus::where('usr_user_id', $usrUserId)
            ->where('mst_mission_daily_bonus_id', 'dailyBonus_1')
            ->first();
        $this->assertNull($usrMissionDailyBonus, 'チュートリアル未完了時はデイリーボーナスが配布されない');

        // イベントデイリーボーナスも配布されていないことを確認
        $usrMissionEventDailyBonus = UsrMissionEventDailyBonus::where('usr_user_id', $usrUserId)
            ->where('mst_mission_event_daily_bonus_id', 'eventDailyBonus_1')
            ->first();
        $this->assertNull($usrMissionEventDailyBonus, 'チュートリアル未完了時はイベントデイリーボーナスが配布されない');

        // 4. チュートリアル進行 (tutorial/update_status) - 1回目 - 3分後
        $this->fixTime('2024-04-01 15:03:00');
        $tutorialResponse1 = $this->sendRequest('tutorial/update_status', [
            'mstTutorialFunctionName' => 'Tutorial1',
        ]);
        $tutorialResponse1->assertStatus(HttpStatusCode::SUCCESS);
        $this->resetAppForNextRequest($usrUserId); // APIリクエスト後にインスタンスをクリア

        // 5. チュートリアル進行 (tutorial/update_status) - 2回目 - 4分後
        $this->fixTime('2024-04-01 15:04:00');
        $tutorialResponse2 = $this->sendRequest('tutorial/update_status', [
            'mstTutorialFunctionName' => 'Tutorial2',
        ]);
        $tutorialResponse2->assertStatus(HttpStatusCode::SUCCESS);
        $this->resetAppForNextRequest($usrUserId); // APIリクエスト後にインスタンスをクリア

        // 6. チュートリアルメインパート完了 (tutorial/update_status) - 3回目 - 5分後
        // この時点でログインカウント更新とログインボーナス配布が行われる
        $this->fixTime('2024-04-01 15:05:00');
        $tutorialResponse3 = $this->sendRequest('tutorial/update_status', [
            'mstTutorialFunctionName' => TutorialFunctionName::MAIN_PART_COMPLETED->value,
        ]);
        $tutorialResponse3->assertStatus(HttpStatusCode::SUCCESS);
        $this->resetAppForNextRequest($usrUserId); // APIリクエスト後にインスタンスをクリア

        // Verify - チュートリアル完了時点でログインカウントが増えていることを確認
        $usrUserLogin = UsrUserLogin::where('usr_user_id', $usrUserId)->first();
        $this->assertNotNull($usrUserLogin, 'チュートリアル完了時にログインカウントが更新される');
        $this->assertEquals(1, $usrUserLogin->getLoginCount(), 'ログインカウントが1になる');
        $this->assertEquals(1, $usrUserLogin->getLoginDayCount(), 'ログイン日数が1になる');
        $this->assertEquals(1, $usrUserLogin->getLoginContinueDayCount(), '連続ログイン日数が1になる');
        $this->assertEquals(0, $usrUserLogin->getComebackDayCount(), '復帰日数は0のまま');
        $this->assertEquals('2024-04-01 15:05:00', $usrUserLogin->getFirstLoginAt(), '初回ログイン日時が記録される');
        $this->assertEquals('2024-04-01 15:05:00', $usrUserLogin->getLastLoginAt(), '最終ログイン日時が記録される');
        $this->assertNotNull($usrUserLogin->getHourlyAccessedAt(), 'hourly_accessed_atは設定されている');

        // Verify - チュートリアル完了時点でデイリーボーナスが配布され、自動受け取りされていることを確認
        $usrMissionDailyBonus = UsrMissionDailyBonus::where('usr_user_id', $usrUserId)
            ->where('mst_mission_daily_bonus_id', 'dailyBonus_1')
            ->first();
        $this->assertNotNull($usrMissionDailyBonus, 'チュートリアル完了時にデイリーボーナスが配布される');
        $this->assertEquals(MissionStatus::RECEIVED_REWARD->value, $usrMissionDailyBonus->getStatus(), 'デイリーボーナスが自動受け取りされる');
        $this->assertNotNull($usrMissionDailyBonus->getClearedAt(), 'クリア日時が記録される');
        $this->assertNotNull($usrMissionDailyBonus->getReceivedRewardAt(), '報酬受取日時が記録される');
        $this->assertEquals('2024-04-01 15:05:00', $usrMissionDailyBonus->getClearedAt(), 'クリア日時が正しい');
        $this->assertEquals('2024-04-01 15:05:00', $usrMissionDailyBonus->getReceivedRewardAt(), '報酬受取日時が正しい');

        // Verify - チュートリアル完了時点でイベントデイリーボーナスも配布され、自動受け取りされていることを確認
        $usrMissionEventDailyBonus = UsrMissionEventDailyBonus::where('usr_user_id', $usrUserId)
            ->where('mst_mission_event_daily_bonus_id', 'eventDailyBonus_1')
            ->first();
        $this->assertNotNull($usrMissionEventDailyBonus, 'チュートリアル完了時にイベントデイリーボーナスが配布される');
        $this->assertEquals(MissionStatus::RECEIVED_REWARD->value, $usrMissionEventDailyBonus->getStatus(), 'イベントデイリーボーナスが自動受け取りされる');
        $this->assertNotNull($usrMissionEventDailyBonus->getClearedAt(), 'イベントデイリーボーナスのクリア日時が記録される');
        $this->assertNotNull($usrMissionEventDailyBonus->getReceivedRewardAt(), 'イベントデイリーボーナスの報酬受取日時が記録される');
        $this->assertEquals('2024-04-01 15:05:00', $usrMissionEventDailyBonus->getClearedAt(), 'イベントデイリーボーナスのクリア日時が正しい');
        $this->assertEquals('2024-04-01 15:05:00', $usrMissionEventDailyBonus->getReceivedRewardAt(), 'イベントデイリーボーナスの報酬受取日時が正しい');

        // Verify - チュートリアル完了レスポンスにデイリーボーナス報酬情報が含まれることを確認
        $tutorialResponse3 = $tutorialResponse3->json();
        $this->assertArrayHasKey('dailyBonusRewards', $tutorialResponse3, 'チュートリアル完了レスポンスにdailyBonusRewardsが含まれる');
        $this->assertCount(1, $tutorialResponse3['dailyBonusRewards'], 'チュートリアル完了レスポンスのdailyBonusRewardsに1件の報酬が含まれる');
        $this->assertArrayHasKey('eventDailyBonusRewards', $tutorialResponse3, 'チュートリアル完了レスポンスにeventDailyBonusRewardsが含まれる');
        $this->assertCount(1, $tutorialResponse3['eventDailyBonusRewards'], 'チュートリアル完了レスポンスのeventDailyBonusRewardsに1件の報酬が含まれる');

        // 7. チュートリアル完了後のゲーム更新・取得 (game/update_and_fetch) - 6分後
        // この時点ではログインカウントは変更されない（既に更新済み）
        $this->fixTime('2024-04-01 15:06:00');
        $gameUpdateResponse = $this->sendRequest('game/update_and_fetch');
        $gameUpdateResponse->assertStatus(HttpStatusCode::SUCCESS);
        $this->resetAppForNextRequest($usrUserId); // APIリクエスト後にインスタンスをクリア

        // レスポンスのデイリーボーナス、イベントデイリーボーナス報酬は空
        $fetchOtherResponse = $gameUpdateResponse->json()['fetchOther'];
        $this->assertArrayHasKey('dailyBonusRewards', $fetchOtherResponse);
        $this->assertEmpty($fetchOtherResponse['dailyBonusRewards'], 'チュートリアル完了時に配布済みなので空');
        $this->assertArrayHasKey('eventDailyBonusRewards', $fetchOtherResponse);
        $this->assertEmpty($fetchOtherResponse['eventDailyBonusRewards'], 'チュートリアル完了時に配布済みなので空');

        // Verify - ログイン回数は増えるが、ログボ配布判定に使うデータは変更されないことを確認
        $usrUserLogin = UsrUserLogin::where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(2, $usrUserLogin->getLoginCount(), 'ゲーム更新後もログインカウントは1のまま');
        $this->assertEquals(1, $usrUserLogin->getLoginDayCount(), 'ゲーム更新後もログイン日数は1のまま');
        $this->assertEquals(1, $usrUserLogin->getLoginContinueDayCount(), 'ゲーム更新後も連続ログイン日数は1のまま');
        $this->assertEquals(0, $usrUserLogin->getComebackDayCount(), 'ゲーム更新後も復帰日数は0のまま');
        $this->assertEquals('2024-04-01 15:05:00', $usrUserLogin->getFirstLoginAt(), 'ゲーム更新後も初回ログイン日時は変更されない');
        $this->assertEquals('2024-04-01 15:06:00', $usrUserLogin->getLastLoginAt(), '最終ログイン日時は6分後のゲーム更新時刻に更新される');
        $this->assertNotNull($usrUserLogin->getHourlyAccessedAt(), 'hourly_accessed_atは設定されている');

        // 8. 翌日日跨ぎ後のゲーム更新・取得 (game/update_and_fetch) - 翌日10:00
        // この時点でログインカウントが進み、2日目のデイリーボーナスが配布される
        $this->fixTime('2024-04-02 10:00:00');
        $nextDayGameUpdateResponse = $this->sendRequest('game/update_and_fetch');
        $nextDayGameUpdateResponse->assertStatus(HttpStatusCode::SUCCESS);
        $this->resetAppForNextRequest($usrUserId); // APIリクエスト後にインスタンスをクリア

        // レスポンスにデイリーボーナス、イベントデイリーボーナス報酬が含まれることを確認
        $nextDayFetchOtherResponse = $nextDayGameUpdateResponse->json()['fetchOther'];
        $this->assertArrayHasKey('dailyBonusRewards', $nextDayFetchOtherResponse);
        $this->assertCount(1, $nextDayFetchOtherResponse['dailyBonusRewards'], '翌日ログイン時に2日目のデイリーボーナスが配布される');
        $this->assertArrayHasKey('eventDailyBonusRewards', $nextDayFetchOtherResponse);
        $this->assertCount(1, $nextDayFetchOtherResponse['eventDailyBonusRewards'], '翌日ログイン時に2日目のイベントデイリーボーナスが配布される');

        // Verify - ログインカウントが更新されていることを確認
        $nextDayUsrUserLogin = UsrUserLogin::where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(3, $nextDayUsrUserLogin->getLoginCount(), '翌日ログイン時にログインカウントが3になる');
        $this->assertEquals(2, $nextDayUsrUserLogin->getLoginDayCount(), '翌日ログイン時にログイン日数が2になる');
        $this->assertEquals(2, $nextDayUsrUserLogin->getLoginContinueDayCount(), '翌日ログイン時に連続ログイン日数が2になる');
        $this->assertEquals(0, $nextDayUsrUserLogin->getComebackDayCount(), '復帰日数は0のまま');
        $this->assertEquals('2024-04-01 15:05:00', $nextDayUsrUserLogin->getFirstLoginAt(), '初回ログイン日時は変更されない');
        $this->assertEquals('2024-04-02 10:00:00', $nextDayUsrUserLogin->getLastLoginAt(), '最終ログイン日時は翌日の時刻に更新される');

        // Verify - データベースに2日目のデイリーボーナスが記録されていることを確認
        $nextDayUsrMissionDailyBonus = UsrMissionDailyBonus::where('usr_user_id', $usrUserId)
            ->where('mst_mission_daily_bonus_id', 'dailyBonus_2')
            ->first();
        $this->assertNotNull($nextDayUsrMissionDailyBonus, '2日目のデイリーボーナスがデータベースに存在する');
        $this->assertEquals(MissionStatus::RECEIVED_REWARD->value, $nextDayUsrMissionDailyBonus->getStatus(), '2日目のデイリーボーナスが自動受け取りされる');
        $this->assertEquals('2024-04-02 10:00:00', $nextDayUsrMissionDailyBonus->getClearedAt(), '2日目のデイリーボーナスのクリア日時が正しい');
        $this->assertEquals('2024-04-02 10:00:00', $nextDayUsrMissionDailyBonus->getReceivedRewardAt(), '2日目のデイリーボーナスの報酬受取日時が正しい');

        // Verify - データベースに2日目のイベントデイリーボーナスが記録されていることを確認
        $nextDayUsrMissionEventDailyBonus = UsrMissionEventDailyBonus::where('usr_user_id', $usrUserId)
            ->where('mst_mission_event_daily_bonus_id', 'eventDailyBonus_2')
            ->first();
        $this->assertNotNull($nextDayUsrMissionEventDailyBonus, '2日目のイベントデイリーボーナスがデータベースに存在する');
        $this->assertEquals(MissionStatus::RECEIVED_REWARD->value, $nextDayUsrMissionEventDailyBonus->getStatus(), '2日目のイベントデイリーボーナスが自動受け取りされる');
        $this->assertEquals('2024-04-02 10:00:00', $nextDayUsrMissionEventDailyBonus->getClearedAt(), '2日目のイベントデイリーボーナスのクリア日時が正しい');
        $this->assertEquals('2024-04-02 10:00:00', $nextDayUsrMissionEventDailyBonus->getReceivedRewardAt(), '2日目のイベントデイリーボーナスの報酬受取日時が正しい');
    }
}
