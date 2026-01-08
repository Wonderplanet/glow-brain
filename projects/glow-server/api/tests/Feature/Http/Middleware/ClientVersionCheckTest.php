<?php

namespace Tests\Feature\Http\Middleware;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mng\Models\MngClientVersion;
use App\Http\Middleware\ClientVersionCheck;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Tests\TestCase;

class ClientVersionCheckTest extends TestCase
{
    private ClientVersionCheck $clientVersionCheck;

    public function setUp(): void
    {
        parent::setUp();
        $this->clientVersionCheck = new ClientVersionCheck();
    }

    public function test_handle_バージョンが登録してありアップデート対象でない場合は通過する()
    {
        // SetUp
        $clientVersion = '1.0.0';
        $platform = 1;
        $response = 'next';

        MngClientVersion::factory()->create([
            'client_version' => $clientVersion,
            'platform' => $platform,
            'is_force_update' => 0,
        ]);

        /** @var Request $mockedRequest */
        $mockedRequest = $this->mock(Request::class, function (MockInterface $mock) use ($clientVersion, $platform) {
            $mock->shouldReceive('header')
                ->with('CLIENT_VERSION')
                ->andReturn((string)$clientVersion);

            $mock->shouldReceive('header')
                ->with('PLATFORM')
                ->andReturn($platform);
        });
        $next = fn() => $response;

        // Exercise
        $result = $this->clientVersionCheck->handle($mockedRequest, $next);

        // Verify
        $this->assertEquals($response, $result);
    }

    public function test_handle_バージョンが未登録の場合は強制アップデートエラーが発生する()
    {
        // SetUp
        $clientVersion = '1.0.0';
        $platform = 1;
        $response = 'next';

        /** @var Request $mockedRequest */
        $mockedRequest = $this->mock(Request::class, function (MockInterface $mock) use ($clientVersion, $platform) {
            $mock->shouldReceive('header')
                ->with('CLIENT_VERSION')
                ->andReturn((string)$clientVersion);

            $mock->shouldReceive('header')
                ->with('PLATFORM')
                ->andReturn($platform);
        });
        $next = fn() => $response;

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::REQUIRE_CLIENT_VERSION_UPDATE);

        // Exercise
        $this->clientVersionCheck->handle($mockedRequest, $next);

        // Verify
    }


    public function test_handle_無効なバージョンの場合は、REQUIRE_CLIENT_VERSION_UPDATEの例外が発生する()
    {
        // SetUp
        $clientVersion = '1.0.0';
        $platform = 1;
        $response = 'next';

        MngClientVersion::factory()->create([
            'client_version' => $clientVersion,
            'platform' => $platform,
            'is_force_update' => 1,
        ]);

        /** @var Request $mockedRequest */
        $mockedRequest = $this->mock(Request::class, function (MockInterface $mock) use ($clientVersion, $platform) {
            $mock->shouldReceive('header')
                ->with('CLIENT_VERSION')
                ->andReturn((string)$clientVersion);

            $mock->shouldReceive('header')
                ->with('PLATFORM')
                ->andReturn($platform);
        });
        $next = fn() => $response;

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::REQUIRE_CLIENT_VERSION_UPDATE);

        // Exercise
        $this->clientVersionCheck->handle($mockedRequest, $next);

        // Verify
    }

    public function test_handle_ヘッダーが存在しない場合は、VALIDATION_ERRORの例外が発生する()
    {
        // SetUp
        $response = 'next';

        /** @var Request $mockedRequest */
        $mockedRequest = $this->mock(Request::class, function (MockInterface $mock) {
            $mock->shouldReceive('header');
        });
        $next = fn() => $response;

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::VALIDATION_ERROR);

        // Exercise
        $this->clientVersionCheck->handle($mockedRequest, $next);

        // Verify
    }

    public function test_handle_ヘッダーのclientVersionが不正なフォーマットの場合は、VALIDATION_ERRORの例外が発生する()
    {
        // SetUp
        $platform = 1;
        $response = 'next';

        MngClientVersion::factory()->create([
            'client_version' => '1.0.0',
            'platform' => $platform,
            'is_force_update' => 0,
        ]);

        /** @var Request $mockedRequest */
        $mockedRequest = $this->mock(Request::class, function (MockInterface $mock) use ($platform) {
            $mock->shouldReceive('header')
                ->with('CLIENT_VERSION')
                ->andReturn('1.invalid-string.0');

            $mock->shouldReceive('header')
                ->with('PLATFORM')
                ->andReturn($platform);
        });
        $next = fn() => $response;

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::VALIDATION_ERROR);

        // Exercise
        $this->clientVersionCheck->handle($mockedRequest, $next);

        // Verify
    }

    public function test_handle_ヘッダーのplatformが不正な値の場合は、VALIDATION_ERRORの例外が発生する()
    {
        // SetUp
        $clientVersion = '1.0.0';
        $response = 'next';

        MngClientVersion::factory()->create([
            'client_version' => $clientVersion,
            'platform' => 1,
            'is_force_update' => 0,
        ]);

        /** @var Request $mockedRequest */
        $mockedRequest = $this->mock(Request::class, function (MockInterface $mock) use ($clientVersion) {
            $mock->shouldReceive('header')
                ->with('CLIENT_VERSION')
                ->andReturn($clientVersion);

            $mock->shouldReceive('header')
                ->with('PLATFORM')
                ->andReturn(555);
        });
        $next = fn() => $response;

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::VALIDATION_ERROR);

        // Exercise
        $this->clientVersionCheck->handle($mockedRequest, $next);

        // Verify
    }
}
