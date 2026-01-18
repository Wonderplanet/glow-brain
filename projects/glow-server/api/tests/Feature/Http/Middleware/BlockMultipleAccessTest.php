<?php

namespace Tests\Feature\Http\Middleware;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Managers\Cache\CacheClientManager;
use App\Http\Middleware\BlockMultipleAccess;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;

class BlockMultipleAccessTest extends TestCase
{
    private BlockMultipleAccess $blockMultipleAccess;

    public function setUp(): void
    {
        parent::setUp();
        $this->blockMultipleAccess = app()->make(BlockMultipleAccess::class);
    }

    public function test_handle_正常実行()
    {
        // SetUp
        $now = $this->fixTime();
        $path = 'api/stage/start';
        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getUsrUserId());
        /** @var Request $request */
        $request = $this->mock(Request::class, function (MockInterface $mock) use ($path, $currentUser) {
            $mock->shouldReceive('path')->andReturn($path);
            $mock->shouldReceive('user')->andReturn($currentUser);
            $mock->shouldReceive('header')
                ->with('Unique-Request-Identifier')
                ->andReturn("test_client_request_id");
        });

        // Exercise
        $this->blockMultipleAccess->handle($request, fn() => 'next');
        $this->assertTrue(true);
    }

    public function test_handle_キャッシュがある場合は二重実行としてエラー()
    {
        // SetUp
        $now = $this->fixTime();
        $path = 'api/stage/start';
        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getUsrUserId());
        /** @var Request $request */
        $request = $this->mock(Request::class, function (MockInterface $mock) use ($path, $currentUser) {
            $mock->shouldReceive('path')->andReturn($path);
            $mock->shouldReceive('user')->andReturn($currentUser);
            $mock->shouldReceive('header')
                ->with('Unique-Request-Identifier')
                ->andReturn("test_client_request_id");
        });
        $cacheClient = app()->make(CacheClientManager::class)->getCacheClient();
        $cacheClient->set("requestidlock:{$user->getUsrUserId()}:test_client_request_id", 1, 30);

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::API_MULTIPLE_ACCESS_ERROR);
        $this->blockMultipleAccess->handle($request, fn() => 'next');
    }

    public function test_handle_別々のリクエストIDの場合は正常()
    {
        // SetUp
        $now = $this->fixTime();
        $path = 'api/stage/start';
        $user = $this->createUsrUser();
        $currentUser = new CurrentUser($user->getUsrUserId());
        /** @var Request $request */
        $request = $this->mock(Request::class, function (MockInterface $mock) use ($path, $currentUser) {
            $mock->shouldReceive('path')->andReturn($path);
            $mock->shouldReceive('user')->andReturn($currentUser);
            $mock->shouldReceive('header')
                ->with('Unique-Request-Identifier')
                ->andReturn("test_client_request_id_1");
        });
        $cacheClient = app()->make(CacheClientManager::class)->getCacheClient();
        $cacheClient->set("requestidlock:{$user->getUsrUserId()}:test_client_request_id_2", 1, 30);

        // Exercise
        $this->blockMultipleAccess->handle($request, fn() => 'next');
        $this->assertTrue(true);
    }

}
