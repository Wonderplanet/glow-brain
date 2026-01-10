<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Utils;

use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Enums\RequestIdType;
use WonderPlanet\Domain\Currency\Utils\CommonUtility;

class CommonUtilityTest extends TestCase
{
    // テスト用にis_debuggable_environmentとenable_sandbox_aggregationを上書きする場合があるので、戻せるように値を保存しておく
    private bool $beforeIsDebuggableEnvironment = false;
    private bool $beforeEnableSandboxAggregation = false;

    public function setUp(): void
    {
        parent::setUp();

        $this->beforeIsDebuggableEnvironment = Config::get('wp_currency.is_debuggable_env');
        $this->beforeEnableSandboxAggregation = Config::get('wp_currency.enable_sandbox_aggregation');
    }

    public function tearDown(): void
    {
        Config::set('wp_currency.is_debuggable_env', $this->beforeIsDebuggableEnvironment);
        Config::set('wp_currency.enable_sandbox_aggregation', $this->beforeEnableSandboxAggregation);

        parent::tearDown();
    }

    public static function getFreeAmountByTypeData(): array
    {
        return [
            'ingame' => ['ingame', 100, [100, 0, 0]],
            'bonus' => ['bonus', 100, [0, 100, 0]],
            'reward' => ['reward', 100, [0, 0, 100]],
        ];
    }

    #[Test]
    #[DataProvider('getFreeAmountByTypeData')]
    public function getFreeAmountByType_無償一次通貨種類による付与数の振り分け($type, $amount, $expected)
    {
        // Exercise
        $amounts = CommonUtility::getFreeAmountByType($type, $amount);

        // Verify
        $this->assertEquals($expected, $amounts);
    }

    #[Test]
    #[DataProvider('enableSandboxAggregationData')]
    public function enableSandboxAggregation_チェック(
        bool $isDebuggableEnv,
        bool $enableSandboxAggregation,
        bool $expected,
    ): void {
        // Setup
        Config::set('wp_currency.is_debuggable_env', $isDebuggableEnv);
        Config::set('wp_currency.enable_sandbox_aggregation', $enableSandboxAggregation);

        // Exercise
        $result = CommonUtility::enableSandboxAggregation();

        // Verify
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public static function enableSandboxAggregationData(): array
    {
        return [
            'デバッグ実行環境ではない、サンドボックスデータ参照環境ではない' => [false, false, false],
            'デバッグ実行環境であり、サンドボックスデータ参照環境ではない' => [true, false, true],
            'デバッグ実行環境ではなく、サンドボックスデータ参照環境である' => [false, true, true],
            'デバッグ実行環境であり、サンドボックスデータ参照環境である' => [true, true, true],
        ];
    }

    #[Test]
    public function calcTtm(): void
    {
        // Exercise
        $actual = CommonUtility::calcTtm("22.24", "23.42");

        // Verify
        $this->assertEquals("22.830000", $actual);
    }

    #[Test]
    #[DataProvider('param_calcTtsOrTtbWithPerUnit')]
    public function calcTtsOrTtbWithPerUnit(string $val, string $perUnit, string $expected): void
    {
        // Exercise
        $actual = CommonUtility::calcTtsOrTtbWithPerUnit($val, $perUnit);

        // Verify
        $this->assertEquals($expected, $actual);
    }

    public static function param_calcTtsOrTtbWithPerUnit()
    {
        return [
            'per 1 unit' => ['11.34', '1unit', '11.340000'],
            'per 100 unit' => ['11.34', '100unit', '0.113400'],
        ];
    }

    #[Test]
    #[DataProvider('param_calcAndRoundRateForTWDAndYMR')]
    public function calcAndRoundRateForTWDAndYMR(string $val, string $perYen, string $expected): void
    {
        // Exercise
        $actual = CommonUtility::calcAndRoundRateForTWDAndYMR($perYen, $val);

        // Verify
        $this->assertEquals($expected, $actual);
    }

    public static function param_calcAndRoundRateForTWDAndYMR()
    {
        return [
            'per 1 yen' => ['11.34', '1', '0.088183'],
            'per 100 yen' => ['11.34', '100', '8.818342'],
        ];
    }
}
