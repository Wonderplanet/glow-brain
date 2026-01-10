<?php

namespace WonderPlanet\Tests\Feature\Domain\Common\Entities;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Common\Factories\LotteryFactory;
use WonderPlanet\Domain\Common\Utils\RandomUtil;

class LotteryTest extends TestCase
{
    use RefreshDatabase;

    private LotteryFactory $lotteryFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lotteryFactory = app(LotteryFactory::class);
    }

    #[Test]
    #[DataProvider('lotParams')]
    public function lot_オッズの設定に従って抽選される(array $lots, int $randomResult, string|int|null $expected)
    {
        // Setup
        $randomUtil = Mockery::mock(RandomUtil::class);
        $randomUtil->shouldReceive('randomInt')
            ->andReturn($randomResult);

        $lotteryFactory = new LotteryFactory($randomUtil);
        $lottery = $lotteryFactory->create($lots);

        // Exercise
        $result = $lottery->draw();

        // Verify
        $this->assertEquals($expected, $result);
    }

    public static function lotParams()
    {
        return [
            '1択/下限値' => [['a' => 100], 0, 'a'],
            '1択/上限値' => [['a' => 100], 100, 'a'],

            // VQでのメモ
            // TODO: 抽選確率が均等になっていないので調整した方がいいかも
            // 具体的には、aとbが50%ずつを意図していたが、実際にはaが51/101、bが50/101の割合になっている

            '2択/下限値' => [['a' => 50, 'b' => 50], 0, 'a'],
            '2択/境界値1' => [['a' => 50, 'b' => 50], 50, 'a'],
            '2択/境界値2' => [['a' => 50, 'b' => 50], 51, 'b'],
            '2択/上限値' => [['a' => 50, 'b' => 50], 100, 'b'],

            '3択/下限値' => [['a' => 50, 'b' => 30, 'c' => 20], 0, 'a'],
            '3択/境界値1' => [['a' => 50, 'b' => 30, 'c' => 20], 50, 'a'],
            '3択/境界値2' => [['a' => 50, 'b' => 30, 'c' => 20], 51, 'b'],
            '3択/境界値3' => [['a' => 50, 'b' => 30, 'c' => 20], 80, 'b'],
            '3択/境界値4' => [['a' => 50, 'b' => 30, 'c' => 20], 81, 'c'],
            '3択/上限値' => [['a' => 50, 'b' => 30, 'c' => 20], 100, 'c'],

            'マイナスのオッズは無視される/下限値' => [['a' => -50, 'b' => 30, 'c' => 20], 0, 'b'],
            'マイナスのオッズは無視される/境界値1' => [['a' => -50, 'b' => 30, 'c' => 20], 30, 'b'],
            'マイナスのオッズは無視される/境界値2' => [['a' => -50, 'b' => 30, 'c' => 20], 31, 'c'],
            'マイナスのオッズは無視される/上限値' => [['a' => -50, 'b' => 30, 'c' => 20], 50, 'c'],
            'オッズが全てマイナスの場合はnull' => [['a' => -50, 'b' => -30, 'c' => -20], 0, null],

            'オッズは小数も設定できる/下限値' => [['a' => 1.5, 'b' => 1.5], 0, 'a'],
            'オッズは小数も設定できる/境界値1' => [['a' => 1.5, 'b' => 1.5], 1, 'a'],
            'オッズは小数も設定できる/境界値2' => [['a' => 1.5, 'b' => 1.5], 2, 'b'],
            'オッズは小数も設定できる/上限値' => [['a' => 1.5, 'b' => 1.5], 3, 'b'],

            'キーに数値も使える' => [[1 => 50, 2 => 50], 0, 1],

            // RandomUtil側に不具合がない限り、このパターンは発生しないはず
            '範囲外1' => [['a' => 50, 'b' => 50], -1, 'a'],
            '範囲外2' => [['a' => 50, 'b' => 50], 101, null],
        ];
    }

    #[Test]
    #[DataProvider('lotParamsWithSeed')]
    public function lot_seedを設定すると毎回同じものが抽選される(array $lots, string $seed, string|int|null $expected)
    {
        // Setup
        $lottery = $this->lotteryFactory->create($lots);

        $passCount = 0;
        $trialCount = 3;

        // Exercise
        for ($i = 0; $i < $trialCount; $i++) {
            $result = $lottery->draw($seed);
            if ($result === $expected) {
                $passCount++;
            }
        }

        // Verify
        $this->assertEquals($trialCount, $passCount);
    }

    public static function lotParamsWithSeed()
    {
        return [
            '1択' => [['a' => 100], '1', 'a'],

            '2択-a' => [['a' => 50, 'b' => 50], '2択-a xxxxxxxa', 'a'],
            '2択-b' => [['a' => 50, 'b' => 50], '2択-b', 'b'],

            '3択-a' => [['a' => 50, 'b' => 30, 'c' => 20], '3択-a-a', 'a'],
            '3択-b' => [['a' => 50, 'b' => 30, 'c' => 20], '3択-b-b', 'b'],
            '3択-c' => [['a' => 50, 'b' => 30, 'c' => 20], '3択-c-ca', 'c'],
        ];
    }

    #[Test]
    public function lot_確率テスト()
    {
        // 指定されない限り、確率テストはスキップする
        if (getenv('RUN_PROBABILITY_TESTS') !== 'true') {
            $this->markTestSkipped('確率テストはスキップされました。');
        }

        // Setup
        $probability = 20; // 期待される確率[%]
        $trials = 10000; // 試行回数
        $confidenceMultiplier = 1.96; // 信頼区間の乗数（ここでは95%信頼区間）
        // 確率の許容誤差を算出
        $standardDeviation = sqrt($trials * ($probability / 100) * (1 - $probability / 100));
        $tolerance = $standardDeviation * $confidenceMultiplier / $trials * 100;

        $lots = [
            'a' => 100 - $probability,
            'b' => $probability,
        ];
        $lottery = $this->lotteryFactory->create($lots);

        // Exercise
        $hits = 0;
        for ($i = 0; $i < $trials; $i++) {
            $result = $lottery->draw();
            if ($result === 'b') {
                $hits++;
            }
        }

        // Verify
        $actualProbability = ($hits / $trials) * 100;

        echo "試行回数: $trials
";
        echo "期待される確率: $probability%
";
        echo "実際の確率: $actualProbability%
";
        echo "許容誤差: +/- $tolerance%
";
        $min = $probability - $tolerance;
        $max = $probability + $tolerance;
        echo "許容誤差の範囲 [ $min, $max ]
";

        $this->assertTrue(abs($actualProbability - $probability) <= $tolerance);
    }
}
