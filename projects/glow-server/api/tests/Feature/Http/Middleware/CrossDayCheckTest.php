<?php

namespace Tests\Feature\Http\Middleware;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\User\Models\UsrUserLogin;
use App\Http\Middleware\CrossDayCheck;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Tests\TestCase;

class CrossDayCheckTest extends TestCase
{
    private CrossDayCheck $crossDayCheck;

    public function setUp(): void
    {
        parent::setUp();
        $this->crossDayCheck = $this->app->make(CrossDayCheck::class);
    }

    public function test_日跨ぎ判定される場合()
    {
        // SetUp
        $now = $this->fixTime();
        $path = 'api/stage/start';
        $user = $this->createUsrUser();
        UsrUserLogin::factory()->create([
            'usr_user_id' => $user->getId(),
            'last_login_at' => $now->subDays(1)->toDateTimeString(),
        ]);
        /** @var Request $request */
        $request = $this->mock(Request::class, function (MockInterface $mock) use ($path, $user) {
            $mock->shouldReceive('path')->andReturn($path);
            $mock->shouldReceive('user')->andReturn($user);
        });

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::CROSS_DAY);
        $this->crossDayCheck->handle($request, fn() => 'next');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_日跨ぎ判定されない場合()
    {
        // SetUp
        $now = $this->fixTime();
        $path = 'api/stage/start';
        $user = $this->createUsrUser();
        UsrUserLogin::factory()->create([
            'usr_user_id' => $user->getId(),
            'last_login_at' => $now->toDateTimeString(),
        ]);
        /** @var Request $request */
        $request = $this->mock(Request::class, function (MockInterface $mock) use ($path, $user) {
            $mock->shouldReceive('path')->andReturn($path);
            $mock->shouldReceive('user')->andReturn($user);
        });

        // Exercise
        $this->crossDayCheck->handle($request, fn() => 'next');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_チェック対象外の場合()
    {
        // SetUp
        $now = $this->fixTime();
        $path = 'api/game/update_and_fetch';
        $user = $this->createUsrUser();
        UsrUserLogin::factory()->create([
            'usr_user_id' => $user->getId(),
            'last_login_at' => $now->subDays(1)->toDateTimeString(),
        ]);
        /** @var Request $request */
        $request = $this->mock(Request::class, function (MockInterface $mock) use ($path, $user) {
            $mock->shouldReceive('path')->andReturn($path);
            $mock->shouldReceive('user')->andReturn($user);
        });

        // Exercise
        $this->crossDayCheck->handle($request, fn() => 'next');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_ユーザー情報が取得できない場合()
    {
        // SetUp
        $now = $this->fixTime();
        $path = 'api/stage/start';
        $user = $this->createUsrUser();
        UsrUserLogin::factory()->create([
            'usr_user_id' => $user->getId(),
            'last_login_at' => $now->subDays(1)->toDateTimeString(),
        ]);
        /** @var Request $request */
        $request = $this->mock(Request::class, function (MockInterface $mock) use ($path) {
            $mock->shouldReceive('path')->andReturn($path);
            $mock->shouldReceive('user')->andReturn(null);
        });

        // Exercise
        $this->crossDayCheck->handle($request, fn() => 'next');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_ユーザーログイン情報が取得できない場合()
    {
        // SetUp
        $path = 'api/stage/start';
        $user = $this->createUsrUser();
        /** @var Request $request */
        $request = $this->mock(Request::class, function (MockInterface $mock) use ($path, $user) {
            $mock->shouldReceive('path')->andReturn($path);
            $mock->shouldReceive('user')->andReturn($user);
        });

        // Exercise
        $this->crossDayCheck->handle($request, fn() => 'next');
    }
}
