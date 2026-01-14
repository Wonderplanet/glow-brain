<?php

namespace Tests\Unit\Services;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use WonderPlanet\Domain\Admin\Services\SendApiService;

class SendApiServiceTest extends TestCase
{
    private SendApiService $sendApiService;

    public function setUp(): void
    {
        parent::setUp();

        $this->sendApiService = app(SendApiService::class);
    }

    /**
     * @test
     */
    public function sendApiRequest_api実行チェック_レスポンスステータスが200(): void
    {
        // Setup
        // モック化して実行する
        $domain = 'http://localhost:8081';
        $endpoint = 'get-master-release-data';
        $mockResponse = [
            'success' => true,
            'data' => [
                'id' => 1,
                'name' => 'Test Data',
            ],
        ];
        Http::fake([$domain . '/api/' . $endpoint => Http::response($mockResponse, 200)]);

        // Exercise
        $actual = $this->sendApiService->sendApiRequest($domain, $endpoint);

        // Verify
        // ステータスが正常だった場合に、想定したモックレスポンスが返されるか
        $this->assertEquals($mockResponse, $actual);
    }

    /**
     * @test
     */
    public function sendApiRequest_api実行チェック_レスポンスステータスが404(): void
    {
        // Setup
        // モック化して実行する
        $domain = 'http://localhost:8081';
        $endpoint = 'get-master-release-data';
        $mockResponse = [
            'error' => 'Not Found',
        ];
        Http::fake([$domain . '/api/' . $endpoint => Http::response($mockResponse, 404)]);

        // Exercise
        $actual = $this->sendApiService->sendApiRequest($domain, $endpoint);

        // Verify
        // ステータスが異常系だった場合に、エラー内容を含んだレスポンスが返されるか
        $this->assertEquals([
            'error' => 'API request failed',
            'status' => 404
        ], $actual);
    }
}
