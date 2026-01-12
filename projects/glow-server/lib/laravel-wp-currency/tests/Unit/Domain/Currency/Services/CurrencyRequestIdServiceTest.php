<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Services;

use Tests\TestCase;
use WonderPlanet\Domain\Currency\Enums\RequestIdType;
use WonderPlanet\Domain\Currency\Services\CurrencyRequestIdService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

class CurrencyRequestIdServiceTest extends TestCase
{
    protected $backupConfigKeys = [
        'wp_common.request_unique_id_header_key',
    ];

    private CurrencyRequestIdService $currencyRequestIdService;

    protected function setUp(): void
    {
        parent::setUp();

        // requestUniqueIdDataを保持しているので、テストの前にリセットする
        $this->currencyRequestIdService = app(CurrencyRequestIdService::class);
    }

    public static function getRequestUniqueIdDataDataProvider(): array
    {
        return [
            'リクエストIDの取得' => [
                'clientUniqueId' => '',
                'frontUniqueId' => '',
                'expectedType' => RequestIdType::Gen,
                'expectedId' => 'api_unique_id',
            ],
            'リクエストIDの取得_ヘッダの設定' => [
                'clientUniqueId' => 'client_unique_id',
                'frontUniqueId' => '',
                'expectedType' => RequestIdType::Product,
                'expectedId' => 'client_unique_id',
            ],
            'リクエストIDの取得_nginxの設定' => [
                'clientUniqueId' => '',
                'frontUniqueId' => 'front_request_id',
                'expectedType' => RequestIdType::Request,
                'expectedId' => 'front_request_id',
            ],
            'リクエストIDの取得_ヘッダとnginxの設定' => [
                'clientUniqueId' => 'client_unique_id',
                'frontUniqueId' => 'front_request_id',
                'expectedType' => RequestIdType::Product,
                'expectedId' => 'client_unique_id',
            ],
        ];
    }

    #[Test]
    #[DataProvider('getRequestUniqueIdDataDataProvider')]
    public function getRequestUniqueIdData_リクエストIDの取得(
        $clientUniqueId,
        $frontUniqueId,
        $expectedType,
        $expectedId,
    )
    {
        // Setup
        // 設定を削除
        $_SERVER['REQUEST_ID'] = '';
        config(['wp_common.request_unique_id_header_key' => 'X-Request-Id']);
        request()->headers->set('X-Request-Id', '');

        // リクエストIDを設定
        if($clientUniqueId !== '') {
            request()->headers->set('X-Request-Id', $clientUniqueId);
        }
        if($frontUniqueId !== '') {
            $_SERVER['REQUEST_ID'] = $frontUniqueId;
        }

        // Exercise
        // 何も設定されていない場合は自動的に設定されたUUIDを返す
        //   $_SERVERはの内容は変更できないため、$_SERVER['REQUEST_ID']のテストはできない
        $requestUniqueIdData = $this->currencyRequestIdService->getRequestUniqueIdData();
        // 2回目の呼び出しでキャッシュされていることを確認
        $requestUniqueIdData2 = $this->currencyRequestIdService->getRequestUniqueIdData();

        // Verify
        // typeがgen:であることを確認
        $this->assertEquals($expectedType, $requestUniqueIdData->getRequestIdType());
        // idが空でないことを確認
        // $expectedTypeがgeの場合はUUIDが生成されているため、空でないことを確認
        if ($expectedType === RequestIdType::Gen) {
            $this->assertNotEmpty($requestUniqueIdData->getRequestId());
        } else {
            $this->assertEquals($expectedId, $requestUniqueIdData->getRequestId());
        }
        // 2回目の呼び出しでキャッシュされていることを確認
        $this->assertEquals($requestUniqueIdData, $requestUniqueIdData2);
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
        $result = $this->currencyRequestIdService->getFrontRequestId();
        // 二回呼び出しても同じ値が返ることを確認
        $result2 = $this->currencyRequestIdService->getFrontRequestId();

        // Verify
        $this->assertEquals($expected, $result);
        $this->assertEquals($expected, $result2);
    }
}
