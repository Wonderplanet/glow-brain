<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Instrumentation\Services;

use Tests\TestCase;
use WonderPlanet\Domain\Instrumentation\Services\DatadogTraceService;
use WonderPlanet\Tests\Traits\Domain\Currency\ReflectionTrait;

class DatadogTraceServiceTest extends TestCase
{
    use ReflectionTrait;

    private DatadogTraceService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app()->make(DatadogTraceService::class);
    }

    private function isExcludeArtisanCommandDataProvider(): array
    {
        return [
            'artisanコマンド' => [
                [
                    'artisan',
                    'command:name',
                ],
                true,
            ],
            'aftisan app:コマンド' => [
                [
                    'artisan',
                    'app:command:name',
                ],
                false,
            ],
            'その他の引数' => [
                // このメソッドはartisanコマンドのみチェックするため、artisanコマンドでなければ対象外
                [
                    'sample',
                    'command:name',
                    'arg1',
                    'arg2',
                ],
                false,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider isExcludeArtisanCommandDataProvider
     */
    public function isExcludeArtisanCommand_トレース除外するartisanコマンドか($argv, $expected): void
    {
        // Exercise
        $actual = $this->callMethod($this->service, 'isExcludeArtisanCommand', $argv);

        // Verify
        $this->assertEquals($expected, $actual);
    }
}
