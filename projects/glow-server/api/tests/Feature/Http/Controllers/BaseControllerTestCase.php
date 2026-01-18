<?php

namespace Tests\Feature\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Arr;
use Mockery\MockInterface;
use Tests\TestCase;

abstract class BaseControllerTestCase extends TestCase
{
    use WithoutMiddleware;

    protected string $baseUrl = 'BASE_URL';

    protected function tearDown(): void
    {
        parent::tearDown();

        // TODO: ログ情報の取得メソッドの実装によっては不要になる
        unset($_SERVER['REQUEST_TIME']);
        unset($_SERVER['REQUEST_ID']);
    }

    protected function mockUseCase(string $class): void
    {
        $this->mock($class, function (MockInterface $mock) {
            $mock->shouldReceive('__invoke');
        });
    }

    protected function mockUseCaseForExec(string $class): void
    {
        $this->mock($class, function (MockInterface $mock) {
            $mock->shouldReceive('exec');
        });
    }

    protected function sendGetRequest(string $url, array $param = [])
    {
        return $this->actingAs($this->createDummyUser())->getJson($this->baseUrl . $url . '?' . Arr::query($param));
    }

    protected function sendRequest(string $url, array $param = [])
    {
        return $this->actingAs($this->createDummyUser())->postJson($this->baseUrl . $url, $param);
    }

    protected function withServerVariable(string $key, string $value): self
    {
        $this->serverVariables[$key] = $value;

        return $this;
    }

    protected function fixTime(?string $dateTime = null): CarbonImmutable
    {
        $now = parent::fixTime($dateTime);

        // Logモデルに保存されるrequestAtの値を固定するためにREQUEST_TIMEを設定する
        // $_SERVERとrequest()->server()から取得される変数が異なる場合があるため、両方に設定している
        // TODO: 取得メソッドを精査して、どちらかに統一する想定
        $this->withServerVariable('REQUEST_TIME', $now->timestamp);
        $_SERVER['REQUEST_TIME'] = $now->timestamp;

        return $now;
    }

    protected function setNginxRequestId(string $requestId): void
    {
        // $_SERVERとrequest()->server()から取得される変数が異なる場合があるため、両方に設定している
        // TODO: 取得メソッドを精査して、どちらかに統一する想定
        $this->withServerVariable('REQUEST_ID', $requestId);
        $_SERVER['REQUEST_ID'] = $requestId;
    }
}
