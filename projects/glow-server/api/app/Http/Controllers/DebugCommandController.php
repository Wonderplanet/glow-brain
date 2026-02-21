<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Common\Constants\System;
use App\Domain\DebugCommand\UseCases\DebugCommandExecUseCase;
use App\Domain\DebugCommand\UseCases\DebugCommandListUseCase;
use App\Http\ResponseFactories\DebugCommandResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DebugCommandController extends Controller
{
    public function __construct(
        readonly private Request $request,
        readonly private DebugCommandResponseFactory $responseFactory,
    ) {
    }

    /**
     * AdminDebugのコマンドリストを返す
     * @param DebugCommandListUseCase $useCase
     * @return JsonResponse
     */
    public function list(DebugCommandListUseCase $useCase): JsonResponse
    {
        // DebugCommandのリストを返す
        $result = $useCase->exec();
        return $this->responseFactory->createDebugCommandListData($result);
    }

    /**
     * AdminDebugで受け取ったコマンドを実行する
     * @param Request $request
     * @param DebugCommandExecUseCase $useCase
     * @return void
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function execute(Request $request, DebugCommandExecUseCase $useCase): void
    {
        //スタミナをマックスにする
        $validated = $request->validate([
            'command' => 'required',
            'params' => 'sometimes|array',
        ]);
        $platform = (int)$request->header(System::HEADER_PLATFORM);

        $params = $validated['params'] ?? [];
        $useCase->exec($this->request->user(), $validated['command'], $platform, $params);
    }
}
