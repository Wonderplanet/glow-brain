<?php

namespace Tests\Feature\Http\Middleware;

use App\Http\Middleware\XsollaSignatureVerification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Mockery\MockInterface;
use Tests\TestCase;

class XsollaSignatureVerificationTest extends TestCase
{
    private XsollaSignatureVerification $middleware;

    public function setUp(): void
    {
        parent::setUp();
        $this->middleware = new XsollaSignatureVerification();
    }

    public function test_handle_正常系_正しい署名でリクエストが通過すること(): void
    {
        // Setup
        $webhookSecret = 'test-secret-key';
        Config::set('services.xsolla.webhook_secret', $webhookSecret);

        $requestBody = '{"notification_type":"user_validation","user":{"id":"test123"}}';
        $expectedSignature = sha1($requestBody . $webhookSecret);
        $authorizationHeader = 'Signature ' . $expectedSignature;

        /** @var Request $mockedRequest */
        $mockedRequest = $this->mock(Request::class, function (MockInterface $mock) use ($authorizationHeader, $requestBody) {
            $mock->shouldReceive('header')
                ->with('Authorization')
                ->andReturn($authorizationHeader);

            $mock->shouldReceive('getContent')
                ->andReturn($requestBody);
        });

        $nextCalled = false;
        $next = function ($request) use (&$nextCalled) {
            $nextCalled = true;
            return 'next response';
        };

        // Exercise
        $result = $this->middleware->handle($mockedRequest, $next);

        // Verify
        $this->assertTrue($nextCalled);
        $this->assertEquals('next response', $result);
    }

    public function test_handle_異常系_Authorizationヘッダーが存在しない場合(): void
    {
        // Setup
        /** @var Request $mockedRequest */
        $mockedRequest = $this->mock(Request::class, function (MockInterface $mock) {
            $mock->shouldReceive('header')
                ->with('Authorization')
                ->andReturn(null);
        });

        $next = fn() => 'next response';

        // Exercise
        $result = $this->middleware->handle($mockedRequest, $next);

        // Verify
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $result);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $result->getStatusCode());

        $data = $result->getData(true);
        $this->assertEquals('INVALID_SIGNATURE', $data['error']['code']);
        $this->assertEquals('Authorization header is missing', $data['error']['message']);
    }

    public function test_handle_異常系_Authorizationヘッダーのフォーマットが不正(): void
    {
        // Setup
        /** @var Request $mockedRequest */
        $mockedRequest = $this->mock(Request::class, function (MockInterface $mock) {
            $mock->shouldReceive('header')
                ->with('Authorization')
                ->andReturn('Bearer invalid-format');
        });

        $next = fn() => 'next response';

        // Exercise
        $result = $this->middleware->handle($mockedRequest, $next);

        // Verify
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $result);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $result->getStatusCode());

        $data = $result->getData(true);
        $this->assertEquals('INVALID_SIGNATURE', $data['error']['code']);
        $this->assertEquals('Invalid Authorization header format. Expected: Signature <value>', $data['error']['message']);
    }

    public function test_handle_異常系_署名値が空の場合(): void
    {
        // Setup
        /** @var Request $mockedRequest */
        $mockedRequest = $this->mock(Request::class, function (MockInterface $mock) {
            $mock->shouldReceive('header')
                ->with('Authorization')
                ->andReturn('Signature ');
        });

        $next = fn() => 'next response';

        // Exercise
        $result = $this->middleware->handle($mockedRequest, $next);

        // Verify
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $result);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $result->getStatusCode());

        $data = $result->getData(true);
        $this->assertEquals('INVALID_SIGNATURE', $data['error']['code']);
        $this->assertEquals('Signature value is empty', $data['error']['message']);
    }

    public function test_handle_異常系_WebhookSecretが設定されていない場合(): void
    {
        // Setup
        Config::set('services.xsolla.webhook_secret', null);

        /** @var Request $mockedRequest */
        $mockedRequest = $this->mock(Request::class, function (MockInterface $mock) {
            $mock->shouldReceive('header')
                ->with('Authorization')
                ->andReturn('Signature test-signature');
        });

        $next = fn() => 'next response';

        // Exercise
        $result = $this->middleware->handle($mockedRequest, $next);

        // Verify
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $result);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $result->getStatusCode());

        $data = $result->getData(true);
        $this->assertEquals('CONFIGURATION_ERROR', $data['error']['code']);
        $this->assertEquals('Webhook secret is not configured', $data['error']['message']);
    }

    public function test_handle_異常系_署名検証が失敗する場合(): void
    {
        // Setup
        $webhookSecret = 'test-secret-key';
        Config::set('services.xsolla.webhook_secret', $webhookSecret);

        $requestBody = '{"notification_type":"user_validation","user":{"id":"test123"}}';
        $invalidSignature = 'invalid-signature-value';
        $authorizationHeader = 'Signature ' . $invalidSignature;

        /** @var Request $mockedRequest */
        $mockedRequest = $this->mock(Request::class, function (MockInterface $mock) use ($authorizationHeader, $requestBody) {
            $mock->shouldReceive('header')
                ->with('Authorization')
                ->andReturn($authorizationHeader);

            $mock->shouldReceive('getContent')
                ->andReturn($requestBody);
        });

        $next = fn() => 'next response';

        // Exercise
        $result = $this->middleware->handle($mockedRequest, $next);

        // Verify
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $result);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $result->getStatusCode());

        $data = $result->getData(true);
        $this->assertEquals('INVALID_SIGNATURE', $data['error']['code']);
        $this->assertEquals('Signature verification failed', $data['error']['message']);
    }

    public function test_handle_正常系_異なるリクエストボディでも正しい署名なら通過すること(): void
    {
        // Setup
        $webhookSecret = 'another-secret-key';
        Config::set('services.xsolla.webhook_secret', $webhookSecret);

        $requestBody = '{"notification_type":"order_paid","order":{"id":12345}}';
        $expectedSignature = sha1($requestBody . $webhookSecret);
        $authorizationHeader = 'Signature ' . $expectedSignature;

        /** @var Request $mockedRequest */
        $mockedRequest = $this->mock(Request::class, function (MockInterface $mock) use ($authorizationHeader, $requestBody) {
            $mock->shouldReceive('header')
                ->with('Authorization')
                ->andReturn($authorizationHeader);

            $mock->shouldReceive('getContent')
                ->andReturn($requestBody);
        });

        $nextCalled = false;
        $next = function ($request) use (&$nextCalled) {
            $nextCalled = true;
            return 'next response';
        };

        // Exercise
        $result = $this->middleware->handle($mockedRequest, $next);

        // Verify
        $this->assertTrue($nextCalled);
        $this->assertEquals('next response', $result);
    }
}
