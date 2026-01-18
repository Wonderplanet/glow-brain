<?php

namespace Tests\Feature\Domain\User\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Emblem\Models\UsrEmblem;
use App\Domain\Resource\Constants\MstConfigConstant;
use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstConfig;
use App\Domain\Resource\Mst\Models\MstEmblem;
use App\Domain\Resource\Mst\Models\MstShopPass;
use App\Domain\Resource\Mst\Models\MstShopPassEffect;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Resource\Mst\Models\MstUserLevelBonus;
use App\Domain\Resource\Mst\Models\MstUserLevelBonusGroup;
use App\Domain\Shop\Enums\PassEffectType;
use App\Domain\Shop\Models\UsrShopPass;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserProfile;
use App\Domain\User\Services\UserService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Crypt;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\Entities\TestLogTrigger;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    private UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = app(UserService::class);
    }

    /**
     * @test
     */
    public function validateStamina_スタミナ不足の場合にLACK_OF_RESOURCESエラーになる()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        // スタミナが1足りない状態にする
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 1,
            'stamina' => 4,
            'stamina_updated_at' => now()->toDateTimeString(),
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 5,
        ]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::LACK_OF_RESOURCES);

        // Exercise
        $result = $this->userService->validateStamina($usrUser->getId(), 5, CarbonImmutable::now());

        // Verify
        $this->assertNull($result);
    }

    public static function params_testConsumeCoin_コイン消費()
    {
        return [
            // LACK_OF_RESOURCESエラーにならない
            'コインが足りている' => [100, 100, false],

            // エラーなく処理が通る
            'コインが足りていない' => [99, 100, true],
        ];
    }
    #[DataProvider('params_testConsumeCoin_コイン消費')]
    public function testConsumeCoin_コイン消費(int $coin, int $consumedCoin, bool $isExceptionThrown)
    {
        $usrUser = $this->createUsrUser();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'coin' => $coin,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 10,
        ]);

        if ($isExceptionThrown) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode(ErrorCode::LACK_OF_RESOURCES);
        }

        $this->userService->consumeCoin($usrUser->getId(), $consumedCoin,$this->fixTime(), new TestLogTrigger());
        $this->saveAll();

        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUser->getId())->first();

        $this->assertEquals($coin - $consumedCoin, $usrUserParameter->getCoin());
    }

    public static function params_testAddCoin_コイン追加()
    {
        return [
            '通常付与' => [9000, 100, 9100],
            'コインがオーバーして最大値以上は切り捨て' => [9999, 100, 9999],
        ];
    }
    #[DataProvider('params_testAddCoin_コイン追加')]
    public function testAddCoin_コイン追加(int $coin, int $addCoin, int $expectedCoin)
    {
        // Setup
        MstConfig::factory()->create([
            'key' => MstConfigConstant::USER_COIN_MAX_AMOUNT,
            'value' => '9999',
        ]);

        $usrUser = $this->createUsrUser();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'coin' => $coin,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 10,
        ]);

        // 報酬データ作成
        $rewards = collect([
            new BaseReward(
                RewardType::COIN->value,
                null,
                $addCoin,
                new LogTriggerDto('test', 'test', 'test')
            ),
        ]);

        // Exercise
        // リワードでコイン付与
        $this->userService->addCoinByRewards($usrUser->getId(), $rewards, $this->fixTime());
        $this->saveAll();

        // Verify
        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals($expectedCoin, $usrUserParameter->getCoin());
    }

    public function test_setNewName_名前が変更できる()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $now = $this->fixTime();
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUser->getId(),
        ]);

        // Exercise
        $newName = "hoge";
        $usrUserProfile = $this->userService->setNewName($usrUser->getId(), $newName, $now);
        $this->saveAllLogModel();

        // Verify
        $this->assertEquals($usrUserProfile->getName(), $newName);

        // DB確認
        $this->assertDatabaseHas(
            'log_user_profiles',
            [
                'usr_user_id' => $usrUser->getId(),
                'profile_column' => 'name',
                'before_value' => '',
                'after_value' => $newName,
            ]
        );
    }

    public function test_setNewName_24時間経過していて名前変更できる()
    {
        // Setup
        $now = $this->fixTime();
        $usrUser = $this->createUsrUser();
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'name_update_at' => $now->subHours(24)->toDateTimeString(),
        ]);

        // Exercise
        $newName = "hoge";
        $usrUserProfile = $this->userService->setNewName($usrUser->getId(), $newName, $now);

        // Verify
        $this->assertEquals($usrUserProfile->getName(), $newName);
    }

    public function test_setNewName_24時間変更していなくて名前変更できない()
    {
        // Setup
        $now = $this->fixTime();
        $usrUser = $this->createUsrUser();
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'name_update_at' => $now->subHours(23)->toDateTimeString(),
        ]);
        $newName = "hoge";

        // error
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::CHANGE_NAME_COOL_TIME);

        // Exercise
        $usrUserProfile = $this->userService->setNewName($usrUser->getId(), $newName, $now);
    }

    public function test_setNewName_24時間変更していないが初回なので名前変更できる()
    {
        // Setup
        $now = $this->fixTime();
        $usrUser = $this->createUsrUser();
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'name' => '', // 初期値
            'name_update_at' => null, // null=初回登録前の状態
        ]);
        $newName = "hoge";

        // Exercise
        $usrUserProfile = $this->userService->setNewName($usrUser->getId(), $newName, $now);

        // Verify
        $this->assertEquals($usrUserProfile->getName(), $newName);
    }

    public function test_setNewName_インターバル時間経過なしで2回目も即時名前変更できる()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'name' => '', // 初期値
            'name_update_at' => null, // null=初回登録前の状態
        ]);

        // 初回はインターバルチェックなしで変更できる
        $now = $this->fixTime('2025-04-02 00:00:01');
        // Exercise
        $newName = "first";
        $usrUserProfile = $this->userService->setNewName($usrUser->getId(), $newName, $now);
        // Verify
        $this->assertEquals($usrUserProfile->getName(), $newName);

        // 2回目はインターバルチェックはあるが、インターバル時間経過なしで変更できる
        $now = $this->fixTime('2025-04-02 00:00:02');
        // Exercise
        $newName = "second";
        $usrUserProfile = $this->userService->setNewName($usrUser->getId(), $newName, $now);
        // Verify
        $this->assertEquals($usrUserProfile->getName(), $newName);

        // 3回目以降はインターバルチェックありで変更できない
        $now = $this->fixTime('2025-04-02 00:00:03');
        // error
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::CHANGE_NAME_COOL_TIME);
        // Exercise
        $newName = "third-ng";
        $usrUserProfile = $this->userService->setNewName($usrUser->getId(), $newName, $now);
    }

    public function test_setNewName_指定時間が登録されていてその時間経過していて名前変更できる()
    {
        // Setup
        $now = $this->fixTime();
        $usrUser = $this->createUsrUser();
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'name_update_at' => $now->subHours(12)->toDateTimeString(),
        ]);

        MstConfig::factory()->create([
            'key' => MstConfigConstant::USER_NAME_CHANGE_INTERVAL_HOURS,
            'value' => 12,
        ]);

        // Exercise
        $newName = "hoge";
        $usrUserProfile = $this->userService->setNewName($usrUser->getId(), $newName, $now);

        // Verify
        $this->assertEquals($usrUserProfile->getName(), $newName);
    }

    public function test_setNewAvatar_アバターが登録できる()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $beforeMstUnitId = "1";
        $mstUnitId = "2";
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_unit_id' => $beforeMstUnitId,
        ]);
        MstUnit::factory()->create([
            'id' => $mstUnitId,
        ]);
        UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_unit_id' => $mstUnitId,
        ]);

        // Exercise
        $usrUserProfile = $this->userService->setNewAvatar($usrUser->getId(), $mstUnitId);
        $this->saveAll();

        // Verify
        $this->assertEquals($usrUserProfile->getMstUnitId(), $mstUnitId);

        // DB確認
        $usrUserProfile = UsrUserProfile::query()->where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals($usrUserProfile->getMstUnitId(), $mstUnitId);
        $this->saveAllLogModel();

        // ログ確認
        $this->assertDatabaseHas(
            'log_user_profiles',
            [
                'usr_user_id' => $usrUser->getId(),
                'profile_column' => 'mst_unit_id',
                'before_value' => $beforeMstUnitId,
                'after_value' => $mstUnitId,
            ]
        );
    }

    public function test_setNewEmblem_エンブレムが登録できる()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $beforeMstEmblemId = "1";
        $mstEmblemId = "2";
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_emblem_id' => $beforeMstEmblemId,
        ]);
        MstEmblem::factory()->create([
            'id' => $mstEmblemId,
        ]);
        UsrEmblem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_emblem_id' => $mstEmblemId,
        ]);

        // Exercise
        $usrUserProfile = $this->userService->setNewEmblem($usrUser->getId(), $mstEmblemId);
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify
        $this->assertEquals($mstEmblemId, $usrUserProfile->getMstEmblemId());

        // DB確認
        $usrUserProfile = UsrUserProfile::query()->where('usr_user_id', $usrUser->getId())->first();
        $this->assertEquals($mstEmblemId, $usrUserProfile->getMstEmblemId());
        $this->saveAllLogModel();

        // ログ確認
        $this->assertDatabaseHas(
            'log_user_profiles',
            [
                'usr_user_id' => $usrUser->getId(),
                'profile_column' => 'mst_emblem_id',
                'before_value' => $beforeMstEmblemId,
                'after_value' => $mstEmblemId,
            ]
        );
    }

    /**
     * @dataProvider provideLevelUpTestParams
     */
    public function test_addExp($exp, $level, $afterExp, $afterAddBonusStamina, $afterAddBonusDiamond, $afterLevelUpAddStamina)
    {
        $now = $this->fixTime();
        $testUserId = $this->createUsrUser()->getId();
        MstUserLevel::factory()->create([
            'level' => 1,
            'stamina' => 10,
            'exp' => 0,
        ]);
        MstUserLevel::factory()->create([
            'level' => 2,
            'stamina' => 10,
            'exp' => 100,
        ]);
        MstUserLevel::factory()->create([
            'level' => 3,
            'stamina' => 10,
            'exp' => 1000,
        ]);
        MstUserLevel::factory()->create([
            'level' => 4,
            'stamina' => 10,
            'exp' => 2000,
        ]);

        MstUserLevelBonus::factory()->create([
            'level' => 3,
            'mst_user_level_bonus_group_id' => 1,
        ]);
        MstUserLevelBonus::factory()->create([
            'level' => 4,
            'mst_user_level_bonus_group_id' => 2,
        ]);

        MstUserLevelBonusGroup::factory()->create([
            'mst_user_level_bonus_group_id' => 1,
            'resource_type' => RewardType::STAMINA->value,
            'resource_id' => null,
            'resource_amount' => 5,
        ]);
        MstUserLevelBonusGroup::factory()->create([
            'mst_user_level_bonus_group_id' => 1,
            'resource_type' => RewardType::FREE_DIAMOND->value,
            'resource_id' => null,
            'resource_amount' => 100,
        ]);
        MstUserLevelBonusGroup::factory()->create([
            'mst_user_level_bonus_group_id' => 2,
            'resource_type' => RewardType::STAMINA->value,
            'resource_id' => null,
            'resource_amount' => 6,
        ]);
        MstUserLevelBonusGroup::factory()->create([
            'mst_user_level_bonus_group_id' => 2,
            'resource_type' => RewardType::FREE_DIAMOND->value,
            'resource_id' => null,
            'resource_amount' => 150,
        ]);

        $baseExp = 500;
        $baseLevel = 2;
        $baseStamina = 10;
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $testUserId,
            'level' => $baseLevel,
            'exp' => $baseExp,
            'stamina' => $baseStamina,
        ]);
        $userLevelUpData = $this->userService->addExp($testUserId, $exp, $now);
        $this->saveAll();
        $this->saveAllLogModel();

        $this->assertEquals($baseExp, $userLevelUpData->beforeExp);
        $this->assertEquals($baseExp + $exp, $userLevelUpData->afterExp);

        $usrUserParameter->refresh();
        // レベルアップ分の報酬が取得できていることを確認
        $this->assertCount(($level - $baseLevel) * 2, $userLevelUpData->levelUpRewards);
        // レベルアップでスタミナが増えていることを確認
        $this->assertEquals($baseStamina + $afterLevelUpAddStamina, $usrUserParameter->getStamina());

        // log_user_levelsテーブルのレコード確認
        $this->assertDatabaseHas('log_user_levels', [
            'usr_user_id' => $testUserId,
            'before_level' => $baseLevel,
            'after_level' => $level,
        ]);
    }

    public function test_addExp_時間回復確認()
    {
        $now = $this->fixTime();
        $testUserId = $this->createUsrUser()->getId();
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 20, 'exp' => 100],
            ['level' => 3, 'stamina' => 30, 'exp' => 1000],
            ['level' => 4, 'stamina' => 40, 'exp' => 2000],
        ]);

        $baseExp = 50;
        $baseLevel = 1;
        $baseStamina = 5;
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $testUserId,
            'level' => $baseLevel,
            'exp' => $baseExp,
            'stamina' => $baseStamina,
            'stamina_updated_at' => $now->subMinutes(UserConstant::RECOVERY_STAMINA_MINUTE * 2)->toDateTimeString(),
        ]);

        $exp = 1000;
        $userLevelUpData = $this->userService->addExp($testUserId, $exp, $now);
        $this->saveAll();
        $this->saveAllLogModel();

        $this->assertEquals($baseExp, $userLevelUpData->beforeExp);
        $this->assertEquals($baseExp + $exp, $userLevelUpData->afterExp);

        $usrUserParameter->refresh();
        // 自然回復分のスタミナ(+2)が加算されていることを確認
        $this->assertEquals(57, $usrUserParameter->getStamina());
    }

    public static function provideLevelUpTestParams()
    {
        // [獲得経験値, 上昇後のレベル, 獲得後の経験値, 報酬獲得後のスタミナ, 報酬獲得後のダイア, レベルアップ時に回復するスタミナ]
        return [
            // レベルアップする
            [1000, 3, 1500, 5, 100, 10],
            // 多段レベルアップ
            [3000, 4, 2000, 11, 250, 20],
            // レベルアップしない
            [10, 2, 510, 0, 0, 0],
        ];
    }

    public function test_addExpMaxLevel()
    {
        $now = $this->fixTime();
        $testUserId = $this->createUsrUser()->getId();
        MstUserLevel::factory()->create([
            'level' => 2,
            'stamina' => 10,
            'exp' => 0,
        ]);

        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $testUserId,
            'level' => 2,
            'exp' => 0,
        ]);

        $userLevelUpData = $this->userService->addExp($testUserId, 10, $now);
        $this->assertEquals(0, $userLevelUpData->beforeExp);
        $this->assertEquals(0, $userLevelUpData->afterExp);
        $this->assertCount(0, $userLevelUpData->levelUpRewards);
    }

    public static function paramsGetRecoveredStamina_自然回復込みのスタミナを取得する()
    {
        $fixedTime = CarbonImmutable::parse('2025-07-15 01:45:23');
        return [
            'スタミナがシステム上限を超えており自然回復時間はない場合' => [
                'stamina' => 1000,
                'staminaUpdatedAt' => $fixedTime->toDateTimeString(),
                'expected' => UserConstant::MAX_STAMINA
            ],
            'スタミナがシステム上限を超えており自然回復時間もある場合' => [
                'stamina' => 1000,
                'staminaUpdatedAt' => $fixedTime->subMinutes(UserConstant::RECOVERY_STAMINA_MINUTE)->toDateTimeString(),
                'expected' => UserConstant::MAX_STAMINA
            ],
            'スタミナがユーザーレベルに応じた上限未満で自然回復を含めても上限以下の場合' => [
                'stamina' => 9,
                'staminaUpdatedAt' => $fixedTime->subMinutes(UserConstant::RECOVERY_STAMINA_MINUTE)->toDateTimeString(),
                'expected' => 9 + 1
            ],
            'スタミナがユーザーレベルに応じた上限未満で自然回復を含めると上限を超える場合' => [
                'stamina' => 9,
                'staminaUpdatedAt' => $fixedTime->subMinutes(UserConstant::RECOVERY_STAMINA_MINUTE * 2)->toDateTimeString(),
                'expected' => 9 + 1
            ],
            'スタミナがユーザーレベルに応じた上限未満で自然回復がない場合' => [
                'stamina' => 9,
                'staminaUpdatedAt' => $fixedTime->toDateTimeString(),
                'expected' => 9
            ],
        ];
    }

    /**
     * @dataProvider paramsGetRecoveredStamina_自然回復込みのスタミナを取得する
     */
    public function testGetRecoveredStamina_自然回復込みのスタミナを取得する(
        int $stamina,
        string $staminaUpdatedAt,
        int $expected
    ) {
        $fixedTime = $this->fixTime('2025-07-15 01:45:23');

        $usrUserId = $this->createUsrUser()->getId();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => $stamina,
            'stamina_updated_at' => $staminaUpdatedAt,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 10
        ]);
        MstConfig::factory()->create([
            'key' => MstConfigConstant::RECOVERY_STAMINA_MINUTE,
            'value' => UserConstant::RECOVERY_STAMINA_MINUTE
        ]);

        $actual = $this->execPrivateMethod($this->userService, 'getRecoveredStamina', [$usrUserId, $fixedTime]);
        $this->assertEquals($expected, $actual);
    }

    public static function paramsCalcStaminaUpdatedAt_自然回復適用後のスタミナ更新時間を計算する()
    {
        $now = CarbonImmutable::now();
        return [
            'スタミナがユーザーレベルに応じた上限を超えている場合' => [
                'stamina' => 11,
                'staminaUpdatedAt' => $now->copy()->subMinutes(UserConstant::RECOVERY_STAMINA_MINUTE)->toDateTimeString(),
                'expected' => $now->copy()->toDateTimeString(),
                'now' => $now
            ],
            'スタミナがユーザーレベルに応じた上限未満で自然回復時間未満の場合' => [
                'stamina' => 9,
                'staminaUpdatedAt' => $now->copy()->subMinutes(UserConstant::RECOVERY_STAMINA_MINUTE - 1)->toDateTimeString(),
                'expected' => $now->copy()->subMinutes(UserConstant::RECOVERY_STAMINA_MINUTE - 1)->toDateTimeString(),
                'now' => $now
            ],
            'スタミナがユーザーレベルに応じた上限未満で自然回復がある場合' => [
                'stamina' => 9,
                'staminaUpdatedAt' => $now->copy()->subMinutes(UserConstant::RECOVERY_STAMINA_MINUTE + 1)->toDateTimeString(),
                'expected' => $now->copy()->subMinutes()->toDateTimeString(),
                'now' => $now
            ],
        ];
    }

    /**
     * @dataProvider paramsCalcStaminaUpdatedAt_自然回復適用後のスタミナ更新時間を計算する
     */
    public function testCalcStaminaUpdatedAt_自然回復適用後のスタミナ更新時間を計算する(
        int $stamina,
        string $staminaUpdatedAt,
        string $expected,
        CarbonImmutable $now
    ) {
        $usrUserId = $this->createUsrUser()->getId();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => $stamina,
            'stamina_updated_at' => $staminaUpdatedAt,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 10
        ]);
        MstConfig::factory()->create([
            'key' => MstConfigConstant::RECOVERY_STAMINA_MINUTE,
            'value' => UserConstant::RECOVERY_STAMINA_MINUTE
        ]);

        $actual = $this->execPrivateMethod($this->userService, 'calcStaminaUpdatedAt', [$usrUserId, $now]);
        $this->assertEquals($expected, $actual);
    }

    public function testRecoveryStamina_スタミナ自然回復を適用する()
    {
        $now = CarbonImmutable::now();
        $diff = 2;
        $usrUserId = $this->createUsrUser()->getId();
        $staminaUpdatedAt = $now
            ->copy()
            ->subMinutes(UserConstant::RECOVERY_STAMINA_MINUTE + $diff)
            ->toDateTimeString();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => 9,
            'stamina_updated_at' => $staminaUpdatedAt,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 10
        ]);
        MstConfig::factory()->create([
            'key' => MstConfigConstant::RECOVERY_STAMINA_MINUTE,
            'value' => UserConstant::RECOVERY_STAMINA_MINUTE
        ]);

        $this->userService->recoveryStamina($usrUserId, $now);
        $this->saveAll();

        $usrUserParameter = $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(10, $usrUserParameter->getStamina());
        $this->assertEquals(
            $now->copy()->subMinutes($diff)->toDateTimeString(),
            $usrUserParameter->getStaminaUpdatedAt()
        );
    }

    public function testRecoveryStamina_スタミナ自然回復がパス効果の上限まで回復する()
    {
        $now = CarbonImmutable::now();
        $mstShopPassId = 'shop_pass_id';
        $diff = 14;
        $usrUserId = $this->createUsrUser()->getId();
        // スタミナを5回復する
        $staminaUpdatedAt = $now
            ->subMinutes(UserConstant::RECOVERY_STAMINA_MINUTE + $diff)
            ->toDateTimeString();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => 9,
            'stamina_updated_at' => $staminaUpdatedAt,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 10
        ]);
        MstConfig::factory()->create([
            'key' => MstConfigConstant::RECOVERY_STAMINA_MINUTE,
            'value' => UserConstant::RECOVERY_STAMINA_MINUTE
        ]);

        MstShopPass::factory()->create([
            'id' => $mstShopPassId,
            'opr_product_id' => 'test',
            'pass_duration_days' => 7,
        ])->toEntity();

        MstShopPassEffect::factory()->createMany([
            // スタミナ回復上限3アップ
            [
                'mst_shop_pass_id' => $mstShopPassId,
                'effect_type' => PassEffectType::STAMINA_ADD_RECOVERY_LIMIT->value,
                'effect_value' => 3,
            ],
        ]);

        UsrShopPass::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_shop_pass_id' => $mstShopPassId,
            'daily_reward_received_count' => 0,
            'daily_latest_received_at' => $now->format('Y-m-d H:i:s'),
            'start_at' => $now->format('Y-m-d H:i:s'),
            'end_at' => $now->addDays(7)->format('Y-m-d H:i:s'),
        ]);

        $this->userService->recoveryStamina($usrUserId, CarbonImmutable::now());
        $this->saveAll();

        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(13, $usrUserParameter->getStamina());
    }

    public function testRecoveryStamina_スタミナ自然回復でパス効果の有効期限が切れている場合()
    {
        $now = CarbonImmutable::now();
        $mstShopPassId = 'shop_pass_id';
        $diff = 14;
        $usrUserId = $this->createUsrUser()->getId();
        // スタミナを5回復する
        $staminaUpdatedAt = $now
            ->subMinutes(UserConstant::RECOVERY_STAMINA_MINUTE + $diff)
            ->toDateTimeString();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => 9,
            'stamina_updated_at' => $staminaUpdatedAt,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 10
        ]);
        MstConfig::factory()->create([
            'key' => MstConfigConstant::RECOVERY_STAMINA_MINUTE,
            'value' => UserConstant::RECOVERY_STAMINA_MINUTE
        ]);

        MstShopPass::factory()->create([
            'id' => $mstShopPassId,
            'opr_product_id' => 'test',
            'pass_duration_days' => 7,
        ])->toEntity();

        MstShopPassEffect::factory()->createMany([
            // スタミナ回復上限3アップ
            [
                'mst_shop_pass_id' => $mstShopPassId,
                'effect_type' => PassEffectType::STAMINA_ADD_RECOVERY_LIMIT->value,
                'effect_value' => 3,
            ],
        ]);

        UsrShopPass::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_shop_pass_id' => $mstShopPassId,
            'daily_reward_received_count' => 0,
            'daily_latest_received_at' => $now->format('Y-m-d H:i:s'),
            'start_at' => $now->subDays(7)->format('Y-m-d H:i:s'),
            'end_at' => $now->subSecond()->format('Y-m-d H:i:s'),
        ]);

        $this->userService->recoveryStamina($usrUserId, CarbonImmutable::now());
        $this->saveAll();

        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        // 上限アップされない
        $this->assertEquals(10, $usrUserParameter->getStamina());
    }

    public static function paramsAddStamina_スタミナを加算する()
    {
        $now = CarbonImmutable::now();
        return [
            '自然回復ありでシステム上限を超える' => [
                'stamina' => 900,
                'staminaUpdatedAt' => $now->copy()->subMinutes(UserConstant::RECOVERY_STAMINA_MINUTE)->toDateTimeString(),
                'addStamina' => 100,
                'expectedStamina' => UserConstant::MAX_STAMINA,
                'now' => $now
            ],
            '自然回復なしでシステム上限を超える' => [
                'stamina' => 900,
                'staminaUpdatedAt' => $now->copy()->toDateTimeString(),
                'addStamina' => 100,
                'expectedStamina' => UserConstant::MAX_STAMINA,
                'now' => $now
            ],
            '自然回復ありでシステム上限を超えない' => [
                'stamina' => 5,
                'staminaUpdatedAt' => $now->copy()->subMinutes(UserConstant::RECOVERY_STAMINA_MINUTE)->toDateTimeString(),
                'addStamina' => 10,
                'expectedStamina' => 5 + 1 + 10,
                'now' => $now
            ],
            '自然回復なしでシステム上限を超えない' => [
                'stamina' => 5,
                'staminaUpdatedAt' => $now->copy()->toDateTimeString(),
                'addStamina' => 10,
                'expectedStamina' => 5 + 10,
                'now' => $now
            ],
        ];
    }

    /**
     * @dataProvider paramsAddStamina_スタミナを加算する
     */
    public function testAddStamina_スタミナを加算する(
        int $stamina,
        string $staminaUpdatedAt,
        int $addStamina,
        int $expectedStamina,
        CarbonImmutable $now
    ) {
        $usrUserId = $this->createUsrUser()->getId();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => $stamina,
            'stamina_updated_at' => $staminaUpdatedAt,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 10
        ]);
        MstConfig::factory()->create([
            'key' => MstConfigConstant::RECOVERY_STAMINA_MINUTE,
            'value' => UserConstant::RECOVERY_STAMINA_MINUTE
        ]);

        $this->userService->addStamina($usrUserId, $addStamina, $now);
        $this->saveAll();

        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($expectedStamina, $usrUserParameter->getStamina());
    }

    public static function paramsConsumeStamina_スタミナを消費する()
    {
        $now = CarbonImmutable::now();
        return [
            '自然回復あり' => [
                'stamina' => 10,
                'staminaUpdatedAt' => $now->copy()->subMinutes(UserConstant::RECOVERY_STAMINA_MINUTE)->toDateTimeString(),
                'consumeStamina' => 5,
                'expectedStamina' => 10 + 1 - 5,
                'now' => $now
            ],
            '自然回復なし' => [
                'stamina' => 10,
                'staminaUpdatedAt' => $now->copy()->toDateTimeString(),
                'consumeStamina' => 5,
                'expectedStamina' => 10 - 5,
                'now' => $now
            ],
        ];
    }

    /**
     * @dataProvider paramsConsumeStamina_スタミナを消費する
     */
    public function testConsumeStamina_スタミナを消費する(
        int $stamina,
        string $staminaUpdatedAt,
        int $consumeStamina,
        int $expectedStamina,
        CarbonImmutable $now
    ) {
        $usrUserId = $this->createUsrUser()->getId();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => $stamina,
            'stamina_updated_at' => $staminaUpdatedAt,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 100
        ]);
        MstConfig::factory()->create([
            'key' => MstConfigConstant::RECOVERY_STAMINA_MINUTE,
            'value' => UserConstant::RECOVERY_STAMINA_MINUTE
        ]);

        $this->userService->consumeStamina($usrUserId, $consumeStamina, $now, new TestLogTrigger());
        $this->saveAll();

        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($expectedStamina, $usrUserParameter->getStamina());
    }

    public function test_recoveryStamina_スタミナ回復量が0の場合はDB更新されないことを確認()
    {
        // Setup
        $now = $this->fixTime('2024-05-07 12:00:00');
        $usrUserId = $this->createUsrUser()->getId();

        // 前回スタミナ回復時刻をスタミナ回復できる直前(1秒前)の時間に設定して、回復しない状態にする
        $staminaUpdatedAt = $now->copy()
            ->addMinutes(UserConstant::RECOVERY_STAMINA_MINUTE)
            ->addSeconds(-1)
            ->toDateTimeString();

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => 1,
            'stamina_updated_at' => $staminaUpdatedAt,
        ]);
        MstUserLevel::factory()->create([
            'level' => 1,
            'stamina' => 100,
        ]);

        // Exercise
        $usrUserParameter = $this->userService->recoveryStamina($usrUserId, $now);
        $this->saveAll();

        // Verify
        // スタミナ回復してないのと、スタミナ回復時刻が更新されていないことを確認
        $this->assertEquals(1, $usrUserParameter->getStamina());
        $this->assertEquals($staminaUpdatedAt, $usrUserParameter->getStaminaUpdatedAt());
    }

    public static function params_test_setBirthDate_生年月日の指定に応じて保存できるまたはできないことを確認()
    {
        return [
            '保存できる 生年月日未登録' => [
                'errorCode' => null,
                'beforeBirthDate' => '',
                'intBirthDate' => 20040701,
            ],
            '保存できない すでに生年月日登録済' => [
                'errorCode' => ErrorCode::USER_BIRTHDATE_ALREADY_REGISTERED,
                'beforeBirthDate' => 'encrypted',
                'intBirthDate' => 20000701,
            ],
            '保存できない 生年月日数字の桁数が多い(想定は8桁)' => [
                'errorCode' => ErrorCode::INVALID_PARAMETER,
                'beforeBirthDate' => '',
                'intBirthDate' => 202407011,
            ],
            '保存できない 生年月日が古すぎる' => [
                'errorCode' => ErrorCode::INVALID_PARAMETER,
                'beforeBirthDate' => '',
                'intBirthDate' => 18000101,
            ],
            '保存できない 生年月日が未来' => [
                'errorCode' => ErrorCode::INVALID_PARAMETER,
                'beforeBirthDate' => '',
                'intBirthDate' => 20240702,
            ],
        ];
    }

    /**
     * @dataProvider params_test_setBirthDate_生年月日の指定に応じて保存できるまたはできないことを確認
     */
    public function test_setBirthDate_生年月日の指定に応じて保存できるまたはできないことを確認(
        ?int $errorCode,
        string $beforeBirthDate,
        int $intBirthDate
    ) {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime('2024-07-01 00:00:00');

        if ($beforeBirthDate === 'encrypted') {
            $beforeBirthDate = Crypt::encryptString($intBirthDate);
        }

        $usrUserProfile = UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => $beforeBirthDate,
        ]);

        if (!is_null($errorCode)) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode($errorCode);
        }

        // Exercise
        $this->userService->setBirthDate($usrUserId, $intBirthDate, $now);
        $this->saveAll();

        // Verify
        $this->assertTrue(true);

        $usrUserProfile->refresh();
        $this->assertNotEquals('', $usrUserProfile->birth_date);
    }
}
