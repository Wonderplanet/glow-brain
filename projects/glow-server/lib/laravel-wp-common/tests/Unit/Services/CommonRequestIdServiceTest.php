<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Serivces;

use Tests\TestCase;
use WonderPlanet\Domain\Common\Services\CommonRequestIdService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * リクエストIDを管理するサービスのテスト
 */
class CommonRequestIdServiceTest extends TestCase
{
    protected $backupConfigKeys = [
        'wp_common.request_unique_id_header_key',
    ];

    private CommonRequestIdService $commonRequestIdService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commonRequestIdService = app()->make(CommonRequestIdService::class);
    }

    #[Test]
    public function getApiRequestId_リクエストID取得()
    {
        // Setup

        // Exercise
        $result = $this->commonRequestIdService->getApiRequestId();
        // 二回呼び出しても同じ値が返ることを確認
        $result2 = $this->commonRequestIdService->getApiRequestId();

        // Verify
        $this->assertEquals($result, $result2);
    }

    #[Test]
    public function getClientRequestId_クライアントのrequest_id取得()
    {
        // Setup
        $requestId = 'client-request-id';
        config(['wp_common.request_unique_id_header_key' => 'Unique-Request-Identifier']);
        request()->headers->set('Unique-Request-Identifier', $requestId);

        // Exercise
        $result = $this->commonRequestIdService->getClientRequestId();
        // 二回呼び出しても同じ値が返ることを確認
        $result2 = $this->commonRequestIdService->getClientRequestId();

        // Verify
        $this->assertEquals($requestId, $result);
        $this->assertEquals($requestId, $result2);
    }

    public static function getFrontRequestIdDataProvider(): array
    {
        return [
            'nginxのrequest_idが取得できる' => [
                'nginx-request-id',
                '',
                'nginx-request-id',
            ],
            'albのrequest_idが取得できる' => [
                '',
                'Root=1-12345678-123456789012345678901234',
                'Root=1-12345678-123456789012345678901234',
            ],
            'nginxのrequest_idとalbのrequest_idが取得できる場合、nginxが優先' => [
                'nginx-request-id',
                'Root=1-12345678-123456789012345678901234',
                'nginx-request-id',
            ],
            'request_idが取得できない' => [
                '',
                '',
                '',
            ],
        ];
    }

    #[Test]
    #[DataProvider('getFrontRequestIdDataProvider')]
    public function getFrontRequestId_フロントのrequest_id取得(
        $nginxRequestId,
        $albRequestId,
        $expected,
    ) {
        // Setup
        // $_SERVER['REQUEST_ID']にnginxのrequest_idをセット
        $_SERVER['REQUEST_ID'] = $nginxRequestId;
        // request()->header()にALBのX-Amzn-Trace-Idをセット
        request()->headers->set('X-Amzn-Trace-Id', $albRequestId);

        // Exercise
        $result = $this->commonRequestIdService->getFrontRequestId();
        // 二回呼び出しても同じ値が返ることを確認
        $result2 = $this->commonRequestIdService->getFrontRequestId();

        // Verify
        $this->assertEquals($expected, $result);
        $this->assertEquals($expected, $result2);
    }
}
