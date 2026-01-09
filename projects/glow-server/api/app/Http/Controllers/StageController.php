<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Common\Constants\System;
use App\Domain\Stage\UseCases\StageAbortUseCase;
use App\Domain\Stage\UseCases\StageCleanupUseCase;
use App\Domain\Stage\UseCases\StageContinueAdUseCase;
use App\Domain\Stage\UseCases\StageContinueDiamondUseCase;
use App\Domain\Stage\UseCases\StageEndUseCase;
use App\Domain\Stage\UseCases\StageStartUseCase;
use App\Http\ResponseFactories\StageResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StageController extends Controller
{
    public function __construct(
        private Request $request,
        private StageResponseFactory $responseFactory,
    ) {
    }

    public function start(StageStartUseCase $useCase, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mstStageId' => 'required',
            'partyNo' => 'required',
            'isChallengeAd' => 'required|boolean',
            'lapCount' => 'sometimes|nullable|integer',
        ]);

        $partyNo = $request->input('partyNo', 0);
        $isChallengeAd = $request->input('isChallengeAd', false);
        $lapCount = $request->input('lapCount') ?? 1;

        $resultData = $useCase->exec(
            $this->request->user(),
            $validated['mstStageId'],
            $partyNo,
            $isChallengeAd,
            $lapCount,
        );

        return $this->responseFactory->createStartResponse($resultData);
    }

    public function end(StageEndUseCase $useCase, Request $request): JsonResponse
    {
        $platform = (int) $request->header(System::HEADER_PLATFORM);

        $validated = $request->validate([
            'mstStageId' => 'required',
            'inGameBattleLog' => 'required',
        ]);

        $inGameBattleLog = $request->input('inGameBattleLog', []);

        $resultData = $useCase->exec($this->request->user(), $platform, $validated['mstStageId'], $inGameBattleLog);

        return $this->responseFactory->createEndResponse($resultData);
    }

    public function continueDiamond(StageContinueDiamondUseCase $useCase, Request $request): JsonResponse
    {
        $platform = (int)$request->header(System::HEADER_PLATFORM);
        $billingPlatform = $request->getBillingPlatform();

        $validated = $request->validate([
            'mstStageId' => 'required',
        ]);

        $resultData = $useCase->exec($this->request->user(), $platform, $validated['mstStageId'], $billingPlatform);

        return $this->responseFactory->createContinueResponse($resultData);
    }

    public function continueAd(StageContinueAdUseCase $useCase, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mstStageId' => 'required',
        ]);

        $resultData = $useCase->exec($this->request->user(), $validated['mstStageId']);

        return $this->responseFactory->createContinueAdResponse($resultData);
    }

    public function abort(StageAbortUseCase $useCase, Request $request): JsonResponse
    {
        $abortType = (int) $request->input('abortType');

        $resultData = $useCase->exec($this->request->user(), $abortType);

        return $this->responseFactory->createAbortResponse($resultData);
    }

    public function cleanup(StageCleanupUseCase $useCase, Request $request): JsonResponse
    {
        $resultData = $useCase->exec($this->request->user());

        return $this->responseFactory->createCleanupResponse($resultData);
    }
}
