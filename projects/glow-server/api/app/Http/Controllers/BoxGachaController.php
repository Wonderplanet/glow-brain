<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\BoxGacha\UseCases\BoxGachaDrawUseCase;
use App\Domain\BoxGacha\UseCases\BoxGachaInfoUseCase;
use App\Domain\BoxGacha\UseCases\BoxGachaResetUseCase;
use App\Domain\Common\Constants\System;
use App\Http\ResponseFactories\BoxGachaResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BoxGachaController extends Controller
{
    /**
     * BOXガチャ情報取得
     *
     * @param Request $request
     * @param BoxGachaInfoUseCase $useCase
     * @param BoxGachaResponseFactory $responseFactory
     * @return JsonResponse
     */
    public function info(
        Request $request,
        BoxGachaInfoUseCase $useCase,
        BoxGachaResponseFactory $responseFactory
    ): JsonResponse {
        $validated = $request->validate([
            'mstBoxGachaId' => 'required|string',
        ]);

        $resultData = $useCase->exec(
            $request->user(),
            $validated['mstBoxGachaId']
        );

        return $responseFactory->createInfoResponse($resultData);
    }

    /**
     * BOXガチャ抽選
     *
     * @param Request $request
     * @param BoxGachaDrawUseCase $useCase
     * @param BoxGachaResponseFactory $responseFactory
     * @return JsonResponse
     */
    public function draw(
        Request $request,
        BoxGachaDrawUseCase $useCase,
        BoxGachaResponseFactory $responseFactory
    ): JsonResponse {
        $validated = $request->validate([
            'mstBoxGachaId' => 'required|string',
            'drawCount' => 'required|integer',
            'currentBoxLevel' => 'required|integer',
        ]);

        $resultData = $useCase->exec(
            $request->user(),
            $validated['mstBoxGachaId'],
            (int)$validated['drawCount'],
            (int)$validated['currentBoxLevel'],
            (int)$request->header(System::HEADER_PLATFORM)
        );

        return $responseFactory->createDrawResponse($resultData);
    }

    /**
     * BOXガチャリセット
     *
     * @param Request $request
     * @param BoxGachaResetUseCase $useCase
     * @param BoxGachaResponseFactory $responseFactory
     * @return JsonResponse
     */
    public function reset(
        Request $request,
        BoxGachaResetUseCase $useCase,
        BoxGachaResponseFactory $responseFactory
    ): JsonResponse {
        $validated = $request->validate([
            'mstBoxGachaId' => 'required|string',
            'currentBoxLevel' => 'required|integer',
        ]);

        $resultData = $useCase->exec(
            $request->user(),
            $validated['mstBoxGachaId'],
            (int)$validated['currentBoxLevel']
        );

        return $responseFactory->createResetResponse($resultData);
    }
}
