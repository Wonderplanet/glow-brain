<?php

namespace Tests\Feature\Http\Middleware;

use App\Domain\Debug\Entities\DebugUserTimeSetting;
use App\Domain\Debug\Entities\DebugUserAllTimeSetting;
use App\Http\Middleware\InitializeDebug;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Mockery\MockInterface;
use Tests\TestCase;

class InitializeDebugTest extends TestCase
{
    private InitializeDebug $initializeDebug;

    public function setUp(): void
    {
        parent::setUp();
        $this->initializeDebug = new InitializeDebug();
    }

    /**
     * @test
     */
    public function handle_サーバ時間変更されている場合は現在時刻が取得されないこと()
    {
        // SetUp
        CarbonImmutable::setTestNow(Carbon::parse('2022-04-07 12:34:56'));
        $user = $this->createUsrUser();
        $response = 'next';
        // キャッシュを追加する(Adminで実装する)
        $setting = new DebugUserAllTimeSetting(
            CarbonImmutable::parse('2030-12-10 00:00:00'),
            CarbonImmutable::now()
        );
        Cache::put('debug:UserAllTime', $setting);

        /** @var Request $request */
        $request = $this->mock(
            Request::class,
            function (MockInterface $mock) use ($user) {
                $mock
                ->shouldReceive('user')
                ->andReturn($user);
            }
        );

        $next = fn() => $response;

        // Exercise
        $result = $this->initializeDebug->handle($request, $next);

        // Verify
        $this->assertEquals('2030-12-10 00:00:00', CarbonImmutable::now()->toDateTimeString());
        $this->assertEquals('2030-12-10 00:00:00', Carbon::now()->toDateTimeString());
        $this->assertEquals($response, $result);
    }

    /**
     * @test
     */
    public function handle_ユーザー時間変更がある場合は全体時間変更より優先される()
    {
        // SetUp
        CarbonImmutable::setTestNow(Carbon::parse('2022-04-07 12:34:56'));
        $user = $this->createUsrUser();
        $response = 'next';
        // キャッシュを追加する(Adminで実装する)
        $setting = new DebugUserAllTimeSetting(
            CarbonImmutable::parse('2030-12-10 00:00:00'),
            CarbonImmutable::now()
        );
        Cache::put('debug:UserAllTime', $setting);

        $setting = new DebugUserTimeSetting(
            CarbonImmutable::parse('2035-11-12 10:20:30'),
            CarbonImmutable::now()
        );
        Cache::put('debug:UserTime:' . $user->getUsrUserId(), $setting);



        /** @var Request $request */
        $request = $this->mock(
            Request::class,
            function (MockInterface $mock) use ($user) {
                $mock
                ->shouldReceive('user')
                ->andReturn($user);
            }
        );

        $next = fn() => $response;

        // Exercise
        $result = $this->initializeDebug->handle($request, $next);

        // Verify
        $this->assertEquals('2035-11-12 10:20:30', CarbonImmutable::now()->toDateTimeString());
        $this->assertEquals('2035-11-12 10:20:30', Carbon::now()->toDateTimeString());
        $this->assertEquals($response, $result);
    }
}
