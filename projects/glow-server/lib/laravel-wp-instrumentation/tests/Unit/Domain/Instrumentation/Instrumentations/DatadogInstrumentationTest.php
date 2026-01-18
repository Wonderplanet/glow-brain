<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Instrumentation\Instrumentations;

use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use Tests\TestCase;
use WonderPlanet\Domain\Instrumentation\Instrumentations\DatadogInstrumentation;
use WonderPlanet\Domain\Instrumentation\Services\DatadogTraceService;
use WonderPlanet\Tests\Traits\Domain\Currency\ReflectionTrait;

/**
 * Datadog向けテスト
 */
class DatadogInstrumentationTest extends TestCase
{
    use ReflectionTrait;

    private DatadogInstrumentation $instrumentation;

    // レストアする設定キーの定義
    protected $backupConfigKeys = [
        'wp_instrumentation.filter.enable_namespaces',
        'wp_instrumentation.filter.exclusion_namespaces',
        'wp_instrumentation.filter.exclusion_start_with_namespaces',
        'wp_instrumentation.filter.exclusion_classes',
        'wp_instrumentation.filter.exclusion_methods.start_with',
        'wp_instrumentation.filter.exclusion_methods.regex',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->instrumentation = app()->make(DatadogInstrumentation::class);
    }

    /**
     * @test
     */
    public function targetTraceList_トレース対象のメソッドリストを取得する(): void
    {
        // Setup
    }

    private function isTargetClassDataProvider(): array
    {
        $namespace = (new ReflectionClass($this))->getNamespaceName();

        return [
            '正常系' => [
                $namespace . '\\DatadogInstrumentationTestSample',
                true
            ],
            'ENABLE_NAMESPACES' => [
                '\\Tests\\TestCase',
                false
            ],
            'EXCLUSION_NAMESPACES' => [
                $namespace . '\\DatadogInstrumentationTestSampleFactory',
                false
            ],
            'EXCLUSION_FULL_NAMESPACES' => [
                'Illuminate\\Support\\Arr',
                false
            ],
            'EXCLUTION_CLASSES' => [
                $namespace . '\\DatadogInstrumentationTestSampleModel',
                false
            ],
            '存在しないクラス' => [
                $namespace . '\\hoge',
                false
            ]
        ];
    }

    /**
     * @test
     * @dataProvider isTargetClassDataProvider
     */
    public function isTargetClass_計測対象のクラスを取得する(string $classname, bool $expected): void
    {
        // Setup
        $enableNamespaces = [
            'WonderPlanet\\Tests\\Unit\\Domain\\Instrumentation\\Instrumentations',
            'Illuminate\\Support',
        ];
        $exclusionNamespaces = [
            '\\\\.*Factory',
        ];
        $exclusionStartWithNamespaces = [
            'Illuminate\\Support\\Arr',
        ];
        $exclusionClasses = [
            \Illuminate\Database\Eloquent\Model::class,
        ];

        // Exercise
        $actual = $this->callMethod(
            $this->instrumentation,
            'isTargetClass',
            $classname,
            $enableNamespaces,
            $exclusionNamespaces,
            $exclusionStartWithNamespaces,
            $exclusionClasses,
        );

        // Verify
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function targetTraceMethodList_計測対象のメソッドを取得する(): void
    {
        // Setup
        $namespace = (new ReflectionClass($this))->getNamespaceName();
        $expected = [
            $namespace . '\\DatadogInstrumentationTestSample' => [
                'sample',
            ],
        ];
        $exclusionMethodsStartWith = [
            $namespace . '\\DatadogInstrumentationTestSample::excludeMethod',
        ];
        $exclusionMethodsRegex = [
            '\\\\.*::__construct',
        ];
        // メソッド内部で参照している設定値を変更する
        config([
            'wp_instrumentation.filter.exclusion_methods.start_with' => $exclusionMethodsStartWith,
            'wp_instrumentation.filter.exclusion_methods.regex' => $exclusionMethodsRegex,
        ]);

        // Exercise
        $actual = $this->callMethod(
            $this->instrumentation,
            'targetTraceMethodList',
            [
                $namespace . '\\DatadogInstrumentationTestSample',
            ]
        );

        // Verify
        $this->assertEquals($expected, $actual);
    }
}

// 実際にクラス定義をしないと判定に使えないため、ここで定義
class DatadogInstrumentationTestSample
{
    public function sample()
    {
        return 'sample';
    }

    public function excludeMethod()
    {
        return 'excludeMethod';
    }

    public function __construct()
    {
    }
}

class DatadogInstrumentationTestSampleFactory
{
    public function sample()
    {
        return 'sample';
    }
}

// EXCLUTION_CLASSESのテスト用のクラス
class DatadogInstrumentationTestSampleModel extends Model {}
