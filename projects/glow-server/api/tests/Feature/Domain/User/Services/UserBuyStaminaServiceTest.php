<?php

namespace Tests\Feature\Domain\User\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Constants\MstConfigConstant;
use App\Domain\Resource\Mst\Models\MstConfig;
use App\Domain\Resource\Mst\Models\MstShopPass;
use App\Domain\Resource\Mst\Models\MstShopPassEffect;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Shop\Enums\PassEffectType;
use App\Domain\Shop\Models\UsrShopPass;
use App\Domain\User\Models\UsrUserBuyCount;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Services\UserBuyStaminaService;
use Carbon\CarbonImmutable;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

class UserBuyStaminaServiceTest extends TestCase
{
    private UserBuyStaminaService $userBuyStaminaService;

    public function setUp(): void
    {
        parent::setUp();
        $this->userBuyStaminaService = $this->app->make(UserBuyStaminaService::class);
    }

    public function testBuyStaminaAd_広告視聴スタミナ購入()
    {
        $usrUserId = $this->createUsrUser()->getId();
        UsrUserBuyCount::factory()->create([
            'usr_user_id' => $usrUserId,
            'daily_buy_stamina_ad_count' => 0,
            'daily_buy_stamina_ad_at' => null,
        ]);
        $maxStamina = 10;
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => $maxStamina - 1,
        ]);
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => $maxStamina]
        ]);
        MstConfig::factory()->createMany([
            ['key' => MstConfigConstant::DAILY_BUY_STAMINA_AD_INTERVAL_MINUTES, 'value' => 10],
            ['key' => MstConfigConstant::MAX_DAILY_BUY_STAMINA_AD_COUNT, 'value' => 3],
            ['key' => MstConfigConstant::BUY_STAMINA_AD_PERCENTAGE_OF_MAX_STAMINA, 'value' => 50],
        ]);

        $now = CarbonImmutable::now();
        $this->userBuyStaminaService->buyStaminaAd($usrUserId, $now);
        $this->saveAll();

        $usrUserBuyCount = UsrUserBuyCount::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(1, $usrUserBuyCount->getDailyBuyStaminaAdCount());
        $this->assertEquals($now->toDateTimeString(), $usrUserBuyCount->getDailyBuyStaminaAdAt());

        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($maxStamina - 1 + 5, $usrUserParameter->getStamina());
    }

    public static function paramsBuyStaminaAd_広告視聴スタミナ購入スタミナ上限エラー()
    {
        return [
            '購入前スタミナがユーザーレベルに応じた上限を超えている' => [
                'stamina' => 10,
                'maxStamina' => 10,
                'errorCode' => ErrorCode::USER_STAMINA_FULL
            ],
            'スタミナ購入後システム上限をこえる' => [
                'stamina' => 666,
                'maxStamina' => 668,
                'errorCode' => ErrorCode::USER_STAMINA_EXCEEDS_LIMIT
            ],
        ];
    }

    /**
     * @dataProvider paramsBuyStaminaAd_広告視聴スタミナ購入スタミナ上限エラー
     */
    public function testBuyStaminaAd_広告視聴スタミナ購入スタミナ上限エラー(int $stamina, int $maxStamina, int $errorCode)
    {
        $usrUserId = $this->createUsrUser()->getId();
        UsrUserBuyCount::factory()->create([
            'usr_user_id' => $usrUserId,
            'daily_buy_stamina_ad_count' => 0,
            'daily_buy_stamina_ad_at' => null,
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => $stamina,
        ]);
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => $maxStamina]
        ]);
        MstConfig::factory()->createMany([
            ['key' => MstConfigConstant::DAILY_BUY_STAMINA_AD_INTERVAL_MINUTES, 'value' => 10],
            ['key' => MstConfigConstant::MAX_DAILY_BUY_STAMINA_AD_COUNT, 'value' => 3],
            ['key' => MstConfigConstant::BUY_STAMINA_AD_PERCENTAGE_OF_MAX_STAMINA, 'value' => 50],
        ]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode($errorCode);

        $now = CarbonImmutable::now();
        $this->userBuyStaminaService->buyStaminaAd($usrUserId, $now);
    }

    public static function params_resetDailyBuyStaminaAdCount_広告視聴スタミナ購入回数リセット検証()
    {
        return [
            '本日初回' => [
                'buyCount' => 0,
                'buyAt' => now()->subDays()->toDateTimeString(),
                'expectedBuyCount' => 0
            ],
            '本日2回目' => [
                'buyCount' => 1,
                'buyAt' => now()->toDateTimeString(),
                'expectedBuyCount' => 1
            ],
        ];
    }

    /**
     * @dataProvider params_resetDailyBuyStaminaAdCount_広告視聴スタミナ購入回数リセット検証
     */
    public function testResetDailyBuyStaminaAdCount_広告視聴スタミナ購入回数リセット検証(
        int $buyCount,
        string $buyAt,
        int $expectedBuyCount
    ) {
        $usrUserId = $this->createUsrUser()->getId();
        $usrUserBuyCount = UsrUserBuyCount::factory()->create([
            'usr_user_id' => $usrUserId,
            'daily_buy_stamina_ad_count' => $buyCount,
            'daily_buy_stamina_ad_at' => $buyAt,
        ]);

        $this->execPrivateMethod(
            $this->userBuyStaminaService,
            'resetDailyBuyStaminaAdCount',
            [$usrUserBuyCount]
        );
        $this->saveAll();

        $this->assertEquals($expectedBuyCount, $usrUserBuyCount->getDailyBuyStaminaAdCount());

        $actual = UsrUserBuyCount::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($expectedBuyCount, $actual->getDailyBuyStaminaAdCount());
    }

    public static function params_validateAvailabilityForBuyStaminaAd_広告視聴スタミナ購入検証()
    {
        return [
            '初回購入' => [
                'buyCount' => 0,
                'buyAt' => null,
                'errorCode' => null
            ],
            '2回目購入' => [
                'buyCount' => 1,
                'buyAt' => now()->subMinutes(10)->toDateTimeString(),
                'errorCode' => null
            ],
            'インターバル中' => [
                'buyCount' => 1,
                'buyAt' => now()->toDateTimeString(),
                'errorCode' => ErrorCode::USER_BUY_STAMINA_AD_DURING_INTERVAL
            ],
            '購入回数上限' => [
                'buyCount' => 3,
                'buyAt' => now()->subDays()->toDateTimeString(),
                'errorCode' => ErrorCode::USER_BUY_STAMINA_COUNT_LIMIT
            ],
        ];
    }

    /**
     * @dataProvider params_validateAvailabilityForBuyStaminaAd_広告視聴スタミナ購入検証
     */
    public function testValidateAvailabilityForBuyStaminaAd_広告視聴スタミナ購入検証(
        int $buyCount,
        ?string $buyAt,
        ?int $errorCode
    ) {
        MstConfig::factory()->createMany([
            ['key' => MstConfigConstant::DAILY_BUY_STAMINA_AD_INTERVAL_MINUTES, 'value' => 10],
            ['key' => MstConfigConstant::MAX_DAILY_BUY_STAMINA_AD_COUNT, 'value' => 3],
        ]);
        $usrUserId = $this->createUsrUser()->getId();
        $usrUserBuyCount = UsrUserBuyCount::factory()->create([
            'usr_user_id' => $usrUserId,
            'daily_buy_stamina_ad_count' => $buyCount,
            'daily_buy_stamina_ad_at' => $buyAt,
        ]);

        if (!is_null($errorCode)) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode($errorCode);
        }

        $this->execPrivateMethod(
            $this->userBuyStaminaService,
            'validateAvailabilityForBuyStaminaAd',
            [$usrUserBuyCount]
        );

        // エラーが起きないテストはassertがないのでダミーでassertを入れる
        $this->assertTrue(true);
    }

    public function testBuyStaminaAd_パス効果ありの場合の広告視聴スタミナ購入()
    {
        $usrUserId = $this->createUsrUser()->getId();
        $mstShopPassId = 'shopPassId1';
        $now = CarbonImmutable::now();
        UsrUserBuyCount::factory()->create([
            'usr_user_id' => $usrUserId,
            'daily_buy_stamina_ad_count' => 0,
            'daily_buy_stamina_ad_at' => null,
        ]);
        $maxStamina = 10;
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => $maxStamina - 1,
        ]);
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => $maxStamina]
        ]);
        MstConfig::factory()->createMany([
            ['key' => MstConfigConstant::DAILY_BUY_STAMINA_AD_INTERVAL_MINUTES, 'value' => 10],
            ['key' => MstConfigConstant::MAX_DAILY_BUY_STAMINA_AD_COUNT, 'value' => 3],
            ['key' => MstConfigConstant::BUY_STAMINA_AD_PERCENTAGE_OF_MAX_STAMINA, 'value' => 50],
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
                'effect_value' => 4,
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

        $this->userBuyStaminaService->buyStaminaAd($usrUserId, $now);
        $this->saveAll();

        $usrUserBuyCount = UsrUserBuyCount::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(1, $usrUserBuyCount->getDailyBuyStaminaAdCount());
        $this->assertEquals($now->toDateTimeString(), $usrUserBuyCount->getDailyBuyStaminaAdAt());

        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($maxStamina - 1 + 7, $usrUserParameter->getStamina());
    }

    public function testBuyStaminaDiamond_ダイヤモンドスタミナ購入()
    {
        $usrUserId = $this->createUsrUser()->getId();
        $this->createDiamond($usrUserId, 100);
        UsrUserBuyCount::factory()->create([
            'usr_user_id' => $usrUserId,
            'daily_buy_stamina_ad_count' => 0,
            'daily_buy_stamina_ad_at' => null,
        ]);
        $maxStamina = 10;
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => $maxStamina - 1,
            'stamina_updated_at' => now()->toDateTimeString(),
        ]);
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => $maxStamina]
        ]);
        MstConfig::factory()->createMany([
            ['key' => MstConfigConstant::BUY_STAMINA_DIAMOND_AMOUNT, 'value' => 100],
            ['key' => MstConfigConstant::BUY_STAMINA_DIAMOND_PERCENTAGE_OF_MAX_STAMINA, 'value' => 100],
        ]);

        $now = CarbonImmutable::now();
        $platform = 1;
        $this->userBuyStaminaService->buyStaminaDiamond(
            $usrUserId,
            $platform,
            CurrencyConstants::PLATFORM_APPSTORE,
            $now
        );
        $this->saveAll();

        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($maxStamina - 1 + $maxStamina, $usrUserParameter->getStamina());

        // 広告での購入回数が加算されていないこと(バグ修正確認のための検証)
        $actual = UsrUserBuyCount::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(0, $actual->getDailyBuyStaminaAdCount());
    }

    public static function paramsBuyStaminaDiamond_ダイヤモンドスタミナ購入スタミナ上限エラー()
    {
        return [
            '購入前スタミナがユーザーレベルに応じた上限を超えている' => [
                'stamina' => 10,
                'maxStamina' => 10,
                'errorCode' => ErrorCode::USER_STAMINA_FULL
            ],
            'スタミナ購入後システム上限をこえる' => [
                'stamina' => 500,
                'maxStamina' => 501,
                'errorCode' => ErrorCode::USER_STAMINA_EXCEEDS_LIMIT
            ],
        ];
    }

    /**
     * @dataProvider paramsBuyStaminaDiamond_ダイヤモンドスタミナ購入スタミナ上限エラー
     */
    public function testBuyStaminaDiamond_ダイヤモンドスタミナ購入スタミナ上限エラー(
        int $stamina,
        int $maxStamina,
        int $errorCode
    ) {
        $usrUserId = $this->createUsrUser()->getId();
        $this->createDiamond($usrUserId, 100);
        UsrUserBuyCount::factory()->create([
            'usr_user_id' => $usrUserId,
            'daily_buy_stamina_ad_count' => 0,
            'daily_buy_stamina_ad_at' => null,
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => $stamina,
            'stamina_updated_at' => now()->toDateTimeString(),
        ]);
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => $maxStamina]
        ]);
        MstConfig::factory()->createMany([
            ['key' => MstConfigConstant::BUY_STAMINA_DIAMOND_AMOUNT, 'value' => 100],
            ['key' => MstConfigConstant::BUY_STAMINA_DIAMOND_PERCENTAGE_OF_MAX_STAMINA, 'value' => 100],
        ]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode($errorCode);

        $now = CarbonImmutable::now();
        $platform = 1;
        $this->userBuyStaminaService->buyStaminaDiamond(
            $usrUserId,
            $platform,
            CurrencyConstants::PLATFORM_APPSTORE,
            $now
        );
    }

    public function testBuyStaminaDiamond_パス効果ありの場合_ダイヤモンドスタミナ購入()
    {
        $usrUserId = $this->createUsrUser()->getId();
        $this->createDiamond($usrUserId, 100);
        $mstShopPassId = 'shopPassId1';
        $now = CarbonImmutable::now();
        UsrUserBuyCount::factory()->create([
            'usr_user_id' => $usrUserId,
            'daily_buy_stamina_ad_count' => 0,
            'daily_buy_stamina_ad_at' => null,
        ]);
        $maxStamina = 10;
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => $maxStamina - 1,
            'stamina_updated_at' => now()->toDateTimeString(),
        ]);
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => $maxStamina]
        ]);
        MstConfig::factory()->createMany([
            ['key' => MstConfigConstant::BUY_STAMINA_DIAMOND_AMOUNT, 'value' => 100],
            ['key' => MstConfigConstant::BUY_STAMINA_DIAMOND_PERCENTAGE_OF_MAX_STAMINA, 'value' => 100],
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
                'effect_value' => 4,
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

        $platform = 1;
        $this->userBuyStaminaService->buyStaminaDiamond(
            $usrUserId,
            $platform,
            CurrencyConstants::PLATFORM_APPSTORE,
            $now
        );
        $this->saveAll();

        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($maxStamina - 1 + $maxStamina + 4, $usrUserParameter->getStamina());

        // 広告での購入回数が加算されていないこと(バグ修正確認のための検証)
        $actual = UsrUserBuyCount::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(0, $actual->getDailyBuyStaminaAdCount());
    }
}
