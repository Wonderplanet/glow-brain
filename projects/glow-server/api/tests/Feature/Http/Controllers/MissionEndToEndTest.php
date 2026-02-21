<?php

namespace Tests\Feature\Http\Controllers;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionDailyBonusType;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Enums\MissionUnlockStatus;
use App\Domain\Mission\Models\UsrMissionDailyBonus;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstIdleIncentive;
use App\Domain\Resource\Mst\Models\MstMissionDailyBonus;
use App\Domain\Resource\Mst\Models\MstTutorial;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Tutorial\Enums\TutorialFunctionName;
use App\Domain\Tutorial\Enums\TutorialType;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserInterface;
use App\Domain\User\Models\UsrUserLogin;
use App\Domain\User\Repositories\UsrUserRepository;
use App\Exceptions\HttpStatusCode;
use Tests\Support\Traits\TestMissionTrait;
use Tests\Support\Traits\TestMultipleApiRequestsTrait;

class MissionEndToEndTest extends BaseControllerTestCase
{
    use TestMissionTrait;
    use TestMultipleApiRequestsTrait;

    protected string $baseUrl = '/api/';

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_updateAndFetch_複数日ログインしてデイリーボーナスのステータスを確認()
    {
        // Setup
        // mst
        MstMissionDailyBonus::factory()->createMany([
            // DailyBonus
            ['id' => 'dailyBonus_dailyBonus_1', 'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS, 'login_day_count' => 1],
            ['id' => 'dailyBonus_dailyBonus_2', 'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS, 'login_day_count' => 2],
            ['id' => 'dailyBonus_dailyBonus_3', 'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS, 'login_day_count' => 3],
        ]);

        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
        ]);
        MstIdleIncentive::factory()->create();

        // Exercise

        $usrUserId = $this->signUpAndSignIn(loginAt: '2024-04-01 15:00:00');

        // チュートリアル完了
        $this->completeTutorial($usrUserId);
        // チュートリアル完了時に初回ログボが配布される: DailyBonusの1日目のミッションをクリア
        $this->checkUsrLoginCount($usrUserId, loginCount: 1, loginDayCount: 1, loginContinueDayCount: 1, comebackDayCount: 0);

        // デイリーボーナスはログイン時に自動受け取りしているので、受け取り済みであることを確認
        // dailyBonus_1
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_1',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-01 15:00:00',
            isReceiveReward: true,
            receivedRewardAt: '2024-04-01 15:00:00'
        );

        // 直後に再ログインしても、ログイン日数もミッション進捗は変わらない
        $this->login(loginAt: '2024-04-01 16:00:00');
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_1',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-01 15:00:00',
            isReceiveReward: true,
            receivedRewardAt: '2024-04-01 15:00:00'
        );
        $this->checkUsrLoginCount($usrUserId, loginCount: 2, loginDayCount: 1, loginContinueDayCount: 1, comebackDayCount: 0);

        // ログイン2日目 1回目: DailyBonusの2日目のミッションをクリア
        $this->login(loginAt: '2024-04-02 15:00:00');
        // dailyBonus_1のステータスは1日目と変わっていない
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_1',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-01 15:00:00',
            isReceiveReward: true,
            receivedRewardAt: '2024-04-01 15:00:00'
        );
        // dailyBonus_2は報酬受取可になっている
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_2',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-02 15:00:00',
            isReceiveReward: false,
            receivedRewardAt: null
        );

        // ログイン2日目 2回目: ミッションステータスは変わらない
        $this->login(loginAt: '2024-04-02 15:30:00');
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_1',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-01 15:00:00',
            isReceiveReward: true,
            receivedRewardAt: '2024-04-01 15:00:00'
        );
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_2',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-02 15:00:00',
            isReceiveReward: false,
            receivedRewardAt: null
        );

        // ログイン3日目 1回目: DailyBonusの3日目のミッションをクリア
        $this->login(loginAt: '2024-04-03 15:00:00');
        // dailyBonus_1のステータスは1日目と変わっていない
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_1',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-01 15:00:00',
            isReceiveReward: true,
            receivedRewardAt: '2024-04-01 15:00:00'
        );
        // dailyBonus_2のステータスは2日目と変わっていない
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_2',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-02 15:00:00',
            isReceiveReward: false,
            receivedRewardAt: null
        );
        // dailyBonus_3は報酬受取可になっている
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_3',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-03 15:00:00',
            isReceiveReward: false,
            receivedRewardAt: null
        );
    }

    public function test_新規登録後に連続ログインが途切れ再度ログインした際に新たな初心者ミッションが開放され達成進捗も進んでいる()
    {
        // Setup
        // mst
        // 1日目ミッション
        $this->createMstMission(MissionType::BEGINNER, 'beginner1-1', MissionCriterionType::LOGIN_COUNT, null, 1, 'rewardGroup1', 0, null, 1);
        $this->createMstMission(MissionType::BEGINNER, 'beginner1-2', MissionCriterionType::COIN_COLLECT, null, 100, null, 0, null, 1);
        // 2日目ミッション
        $this->createMstMission(MissionType::BEGINNER, 'beginner2-1', MissionCriterionType::LOGIN_COUNT, null, 2, null, 0, null, 2);
        $this->createMstMission(MissionType::BEGINNER, 'beginner2-2', MissionCriterionType::COIN_COLLECT, null, 200, null, 0, null, 2);
        // 3日目ミッション
        $this->createMstMission(MissionType::BEGINNER, 'beginner3-1', MissionCriterionType::LOGIN_COUNT, null, 3, null, 0, null, 3);
        $this->createMstMission(MissionType::BEGINNER, 'beginner3-2', MissionCriterionType::COIN_COLLECT, null, 300, null, 0, null, 3);
        // 報酬設定
        $this->createMstReward('rewardGroup1', RewardType::COIN, null, 250);

        /**
         * 1日目（新規登録日）
         */
        $now = $this->fixTime('2024-04-01 15:00:00');
        // sign_up
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
        ]);
        MstIdleIncentive::factory()->create();

        // Exercise
        // sign_up, sign_in
        $usrUserId = $this->signUpAndSignIn($now->format('Y-m-d H:i:s'));
        // チュートリアル完了してログインカウントが1になり、カウントが進むようにする
        $this->completeTutorial($usrUserId);
        // game/update_and_fetch
        $response = $this->sendRequest('game/update_and_fetch');
        $response->assertStatus(HttpStatusCode::SUCCESS);
        // mission/update_and_fetch
        $response = $this->sendRequest('mission/update_and_fetch');
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $this->assertEqualsCanonicalizing(
            [
                'beginner1-1',
                'beginner2-1',
                'beginner3-1',
            ],
            collect($response->json()['usrMissionBeginners'])->pluck('mstMissionBeginnerId')->all(),
        );
        // beginner1-1報酬受け取り
        $response = $this->sendRequest(
            'mission/bulk_receive_reward',
            [
                'missionType' => MissionType::BEGINNER->value,
                'mstMissionIds' => ['beginner1-1'],
            ]
        );
        $response->assertStatus(HttpStatusCode::SUCCESS);
        // mission/update_and_fetch
        $response = $this->sendRequest('mission/update_and_fetch');
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $this->assertEqualsCanonicalizing(
            [
                'beginner1-1',
                'beginner1-2',
                'beginner2-1',
                'beginner2-2',
                'beginner3-1',
                'beginner3-2',
            ],
            collect($response->json()['usrMissionBeginners'])->pluck('mstMissionBeginnerId')->all(),
        );
        // Exercise
        $usrMissions = $this->getMstMissionIdUsrMissionNormalMap($usrUserId, MissionType::BEGINNER);
        $this->assertCount(6, $usrMissions);
        // ログインしようミッションが進捗更新している
        $this->checkUsrMissionNormal($usrMissions['beginner1-1'], MissionStatus::RECEIVED_REWARD, 1, $now, $now, $now, MissionUnlockStatus::OPEN, 1);
        $this->checkUsrMissionNormal($usrMissions['beginner2-1'], MissionStatus::UNCLEAR, 1, $now, null, null, MissionUnlockStatus::LOCK, 1);
        $this->checkUsrMissionNormal($usrMissions['beginner3-1'], MissionStatus::UNCLEAR, 1, $now, null, null, MissionUnlockStatus::LOCK, 1);
        // コイン集めようミッションが進捗更新している
        $this->checkUsrMissionNormal($usrMissions['beginner1-2'], MissionStatus::CLEAR, 100, $now, $now, null, MissionUnlockStatus::OPEN, 1);
        $this->checkUsrMissionNormal($usrMissions['beginner2-2'], MissionStatus::CLEAR, 200, $now, $now, null, MissionUnlockStatus::LOCK, 1);
        $this->checkUsrMissionNormal($usrMissions['beginner3-2'], MissionStatus::UNCLEAR, 250, $now, null, null, MissionUnlockStatus::LOCK, 1);

        /**
         * 2日目
         */
        $yesterday = clone $now;
        $now = $this->fixTime('2024-04-02 15:00:00');
        // game/update_and_fetch
        $response = $this->sendRequest('game/update_and_fetch');
        $response->assertStatus(HttpStatusCode::SUCCESS);
        // 2日目のミッションが進捗値を保ったまま開放されている
        $usrMissions = $this->getMstMissionIdUsrMissionNormalMap($usrUserId, MissionType::BEGINNER);
        $this->assertCount(6, $usrMissions);
        // ログインしようミッションが進捗更新している
        $this->checkUsrMissionNormal($usrMissions['beginner1-1'], MissionStatus::RECEIVED_REWARD, 1, $yesterday, $yesterday, $yesterday, MissionUnlockStatus::OPEN, 1);
        $this->checkUsrMissionNormal($usrMissions['beginner2-1'], MissionStatus::CLEAR, 2, $yesterday, $now, null, MissionUnlockStatus::OPEN, 2);
        $this->checkUsrMissionNormal($usrMissions['beginner3-1'], MissionStatus::UNCLEAR, 2, $yesterday, null, null, MissionUnlockStatus::LOCK, 2);
        // コイン集めようミッションが進捗更新している
        $this->checkUsrMissionNormal($usrMissions['beginner1-2'], MissionStatus::CLEAR, 100, $yesterday, $yesterday, null, MissionUnlockStatus::OPEN, 1);
        $this->checkUsrMissionNormal($usrMissions['beginner2-2'], MissionStatus::CLEAR, 200, $yesterday, $yesterday, null, MissionUnlockStatus::OPEN, 2);
        $this->checkUsrMissionNormal($usrMissions['beginner3-2'], MissionStatus::UNCLEAR, 250, $yesterday, null, null, MissionUnlockStatus::LOCK, 1);
        // mission/update_and_fetch
        $response = $this->sendRequest('mission/update_and_fetch');
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $this->assertEqualsCanonicalizing(
            [
                'beginner1-1',
                'beginner1-2',
                'beginner2-1',
                'beginner2-2',
                'beginner3-1',
                'beginner3-2',
            ],
            collect($response->json()['usrMissionBeginners'])->pluck('mstMissionBeginnerId')->all(),
        );
    }

    public function test_updateAndFetch_7日間ログインしてデイリーボーナスのミッション報酬を受け取り14日間までログインを継続できること()
    {
        // Setup
        // mst
        MstMissionDailyBonus::factory()->createMany([
            // DailyBonus 1日目から7日目
            ['id' => 'dailyBonus_dailyBonus_1', 'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS, 'login_day_count' => 1],
            ['id' => 'dailyBonus_dailyBonus_2', 'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS, 'login_day_count' => 2],
            ['id' => 'dailyBonus_dailyBonus_3', 'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS, 'login_day_count' => 3],
            ['id' => 'dailyBonus_dailyBonus_4', 'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS, 'login_day_count' => 4],
            ['id' => 'dailyBonus_dailyBonus_5', 'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS, 'login_day_count' => 5],
            ['id' => 'dailyBonus_dailyBonus_6', 'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS, 'login_day_count' => 6],
            ['id' => 'dailyBonus_dailyBonus_7', 'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS, 'login_day_count' => 7],
        ]);

        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
        ]);
        MstIdleIncentive::factory()->create();

        // Exercise
        $usrUserId = $this->signUpAndSignIn(loginAt: '2024-04-01 15:00:00');

        // チュートリアル完了
        $this->completeTutorial($usrUserId);
        // チュートリアル完了時に初回ログボが配布される: DailyBonusの1日目のミッションをクリア
        $this->checkUsrLoginCount($usrUserId, loginCount: 1, loginDayCount: 1, loginContinueDayCount: 1, comebackDayCount: 0);

        // 1日目のミッションは自動で報酬を受け取っていること
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_1',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-01 15:00:00',
            isReceiveReward: true,
            receivedRewardAt: '2024-04-01 15:00:00'
        );

        // ログイン 2日目: 2日目のデイリーボーナスミッションをクリア
        $this->login(loginAt: '2024-04-02 15:00:00');
        $this->checkUsrLoginCount($usrUserId, loginCount: 2, loginDayCount: 2, loginContinueDayCount: 2, comebackDayCount: 0);

        // 1日目のミッションステータスは変わらない
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_1',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-01 15:00:00',
            isReceiveReward: true,
            receivedRewardAt: '2024-04-01 15:00:00'
        );

        // 2日目のミッションは報酬受取可になっている
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_2',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-02 15:00:00',
            isReceiveReward: false,
            receivedRewardAt: null
        );

        // ログイン 3日目: 3日目のデイリーボーナスミッションをクリア
        $this->login(loginAt: '2024-04-03 15:00:00');
        $this->checkUsrLoginCount($usrUserId, loginCount: 3, loginDayCount: 3, loginContinueDayCount: 3, comebackDayCount: 0);

        // 3日目のミッションは報酬受取可になっている
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_3',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-03 15:00:00',
            isReceiveReward: false,
            receivedRewardAt: null
        );

        // ログイン 4日目: 4日目のデイリーボーナスミッションをクリア
        $this->login(loginAt: '2024-04-04 15:00:00');
        $this->checkUsrLoginCount($usrUserId, loginCount: 4, loginDayCount: 4, loginContinueDayCount: 4, comebackDayCount: 0);

        // 4日目のミッションは報酬受取可になっている
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_4',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-04 15:00:00',
            isReceiveReward: false,
            receivedRewardAt: null
        );

        // ログイン 5日目: 5日目のデイリーボーナスミッションをクリア
        $this->login(loginAt: '2024-04-05 15:00:00');
        $this->checkUsrLoginCount($usrUserId, loginCount: 5, loginDayCount: 5, loginContinueDayCount: 5, comebackDayCount: 0);

        // 5日目のミッションは報酬受取可になっている
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_5',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-05 15:00:00',
            isReceiveReward: false,
            receivedRewardAt: null
        );

        // ログイン 6日目: 6日目のデイリーボーナスミッションをクリア
        $this->login(loginAt: '2024-04-06 15:00:00');
        $this->checkUsrLoginCount($usrUserId, loginCount: 6, loginDayCount: 6, loginContinueDayCount: 6, comebackDayCount: 0);

        // 6日目のミッションは報酬受取可になっている
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_6',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-06 15:00:00',
            isReceiveReward: false,
            receivedRewardAt: null
        );

        // ログイン 7日目: 7日目のデイリーボーナスミッションをクリアして報酬を受け取る
        $this->login(loginAt: '2024-04-07 15:00:00');
        $this->checkUsrLoginCount($usrUserId, loginCount: 7, loginDayCount: 7, loginContinueDayCount: 7, comebackDayCount: 0);

        // 7日目のミッションは報酬受取可になっている
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_7',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-07 15:00:00',
            isReceiveReward: false,
            receivedRewardAt: null
        );

        // 7日目のミッションの報酬を受け取る
        $response = $this->sendRequest(
            'mission/bulk_receive_reward',
            [
                'missionType' => MissionType::DAILY_BONUS->value,
                'mstMissionIds' => ['dailyBonus_dailyBonus_7'],
            ]
        );
        $response->assertStatus(HttpStatusCode::SUCCESS);

        // 7日目のミッションの報酬が受け取り済みであること
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_7',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-07 15:00:00',
            isReceiveReward: true,
            receivedRewardAt: '2024-04-07 15:00:00'
        );

        // ログイン 8日目: 一周して1日目のミッションがリセットされて再度クリアできる
        $this->login(loginAt: '2024-04-08 15:00:00');
        $this->checkUsrLoginCount($usrUserId, loginCount: 8, loginDayCount: 8, loginContinueDayCount: 8, comebackDayCount: 0);

        // 1日目のミッションが再度クリア可能になっている（第2週目の1日目）
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_1',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-08 15:00:00',
            isReceiveReward: false,
            receivedRewardAt: null
        );

        // ログイン 9日目: 2日目のミッションが再度クリア可能
        $this->login(loginAt: '2024-04-09 15:00:00');
        $this->checkUsrLoginCount($usrUserId, loginCount: 9, loginDayCount: 9, loginContinueDayCount: 9, comebackDayCount: 0);

        // 2日目のミッションが再度クリア可能になっている（第2週目の2日目）
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_2',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-09 15:00:00',
            isReceiveReward: false,
            receivedRewardAt: null
        );

        // ログイン 10日目: 3日目のミッションが再度クリア可能
        $this->login(loginAt: '2024-04-10 15:00:00');
        $this->checkUsrLoginCount($usrUserId, loginCount: 10, loginDayCount: 10, loginContinueDayCount: 10, comebackDayCount: 0);

        // 3日目のミッションが再度クリア可能になっている（第2週目の3日目）
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_3',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-10 15:00:00',
            isReceiveReward: false,
            receivedRewardAt: null
        );

        // ログイン 11日目: 4日目のミッションが再度クリア可能
        $this->login(loginAt: '2024-04-11 15:00:00');
        $this->checkUsrLoginCount($usrUserId, loginCount: 11, loginDayCount: 11, loginContinueDayCount: 11, comebackDayCount: 0);

        // 4日目のミッションが再度クリア可能になっている（第2週目の4日目）
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_4',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-11 15:00:00',
            isReceiveReward: false,
            receivedRewardAt: null
        );

        // ログイン 12日目: 5日目のミッションが再度クリア可能
        $this->login(loginAt: '2024-04-12 15:00:00');
        $this->checkUsrLoginCount($usrUserId, loginCount: 12, loginDayCount: 12, loginContinueDayCount: 12, comebackDayCount: 0);

        // 5日目のミッションが再度クリア可能になっている（第2週目の5日目）
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_5',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-12 15:00:00',
            isReceiveReward: false,
            receivedRewardAt: null
        );

        // ログイン 13日目: 6日目のミッションが再度クリア可能
        $this->login(loginAt: '2024-04-13 15:00:00');
        $this->checkUsrLoginCount($usrUserId, loginCount: 13, loginDayCount: 13, loginContinueDayCount: 13, comebackDayCount: 0);

        // 6日目のミッションが再度クリア可能になっている（第2週目の6日目）
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_6',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-13 15:00:00',
            isReceiveReward: false,
            receivedRewardAt: null
        );

        // ログイン 14日目: 7日目のミッションが再度クリア可能
        $this->login(loginAt: '2024-04-14 15:00:00');
        $this->checkUsrLoginCount($usrUserId, loginCount: 14, loginDayCount: 14, loginContinueDayCount: 14, comebackDayCount: 0);

        // 7日目のミッションが再度クリア可能になっている（第2週目の7日目）
        $this->checkUsrMissionStatus(
            $usrUserId,
            'dailyBonus_dailyBonus_7',
            isExist: true,
            isClear: true,
            clearedAt: '2024-04-14 15:00:00',
            isReceiveReward: false,
            receivedRewardAt: null
        );
    }

    private function signUpAndSignIn(string $loginAt): string
    {
        $this->fixTime($loginAt);

        // 新規アカウント登録
        $result = $this->sendRequest('sign_up');
        $result->assertStatus(HttpStatusCode::SUCCESS);
        $this->assertArrayHasKey('id_token', $result);
        $idToken = $result['id_token'];

        $usrUserId = UsrUser::all()->first()->getId();
        $this->setUsrUserId($usrUserId);

        // サインイン
        $result = $this->sendRequest('sign_in', ['id_token' => $idToken]);
        $result->assertStatus(HttpStatusCode::SUCCESS);

        return $usrUserId;
    }

    private function completeTutorial(string $usrUserId): void
    {
        // チュートリアルマスターデータ作成
        MstTutorial::factory()->createMany([
            [
                'type' => TutorialType::INTRO,
                'sort_order' => 1,
                'function_name' => TutorialFunctionName::GACHA_CONFIRMED->value,
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-31 23:59:59',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 2,
                'function_name' => TutorialFunctionName::START_MAIN_PART3->value,
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

        // チュートリアル進行 - 1回目
        $this->sendRequest('tutorial/update_status', ['mstTutorialFunctionName' => TutorialFunctionName::GACHA_CONFIRMED->value]);
        $this->resetAppForNextRequest($usrUserId);

        // チュートリアル進行 - 2回目
        $this->sendRequest('tutorial/update_status', ['mstTutorialFunctionName' => TutorialFunctionName::START_MAIN_PART3->value]);
        $this->resetAppForNextRequest($usrUserId);

        // チュートリアルメインパート完了 - 3回目
        $this->sendRequest('tutorial/update_status', ['mstTutorialFunctionName' => TutorialFunctionName::MAIN_PART_COMPLETED->value]);
        $this->resetAppForNextRequest($usrUserId);
    }

    private function login(string $loginAt): void
    {
        $this->fixTime($loginAt);
        $response = $this->sendRequest('game/update_and_fetch');
        $response->assertStatus(HttpStatusCode::SUCCESS);

        $response = $this->sendRequest('mission/update_and_fetch');
        $response->assertStatus(HttpStatusCode::SUCCESS);
    }

    private function checkUsrMissionStatus(
        string $usrUserId,
        string $mstMissionId,
        bool $isExist,
        bool $isClear,
        ?string $clearedAt,
        bool $isReceiveReward,
        ?string $receivedRewardAt
    ): void {
        $usrMission = UsrMissionDailyBonus::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_mission_daily_bonus_id', $mstMissionId)
            ->first();

        if ($isExist) {
            $this->assertNotNull($usrMission, 'not exist');
        } else {
            $this->assertNull($usrMission, 'exist');
            return;
        }

        if ($isClear) {
            $this->assertEquals(MissionStatus::CLEAR->value, $usrMission->isClear(), 'clear');
            $this->assertEquals($clearedAt, $usrMission->getClearedAt(), 'clearAt');
        } else {
            $this->assertEquals(MissionStatus::UNCLEAR->value, $usrMission->getStatus(), 'unclear');
            $this->assertEquals($clearedAt, $usrMission->getClearedAt(), 'clearAt');
        }

        if ($isReceiveReward) {
            $this->assertEquals(MissionStatus::RECEIVED_REWARD->value, $usrMission->getStatus(), 'reward received');
            $this->assertEquals($receivedRewardAt, $usrMission->getReceivedRewardAt(), 'receivedRewardAt');
        }
    }

    private function checkUsrLoginCount(
        string $usrUserId,
        int $loginCount,
        int $loginDayCount,
        int $loginContinueDayCount,
        int $comebackDayCount
    ): void {
        $usrUserLogin = UsrUserLogin::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertNotNull($usrUserLogin);
        $this->assertEquals($loginCount, $usrUserLogin->getLoginCount());
        $this->assertEquals($loginDayCount, $usrUserLogin->getLoginDayCount());
        $this->assertEquals($loginContinueDayCount, $usrUserLogin->getLoginContinueDayCount());
        $this->assertEquals($comebackDayCount, $usrUserLogin->getComebackDayCount());
    }
}
