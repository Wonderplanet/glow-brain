<?php

namespace Tests\Feature\Http\Controllers;

use App\Domain\Common\Constants\System;
use App\Domain\DebugCommand\UseCases\DebugCommandExecUseCase;
use App\Exceptions\HttpStatusCode;

class DebugCommandControllerTest extends BaseControllerTestCase
{
    protected string $baseUrl = '/api/debug_command/';

    /**
     * @test
     */
    public function list_リクエストを送ると200OKが返ることを確認する()
    {
        if (config('app.debug') === false) {
             $this->markTestSkipped('デバッグ設定になっていないためスキップ');
        }

        // Exercise
        $response = $this->sendGetRequest('list');

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
    }

    /**
     * @test
     */
    public function execute_リクエストを送ると200OKが返ることを確認する()
    {
        if (config('app.debug') === false) {
            $this->markTestSkipped('デバッグ設定になっていないためスキップ');
        }

        // Setup
        $this->mockUseCaseForExec(DebugCommandExecUseCase::class);
        $param = [
            'command' => 'TestCommand'
        ];

        // Exercise
        $response = $this->withHeaders([
            System::HEADER_PLATFORM => System::PLATFORM_IOS,
        ])->sendRequest('execute', $param);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);
    }
}
