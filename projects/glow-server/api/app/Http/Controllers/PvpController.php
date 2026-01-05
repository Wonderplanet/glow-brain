<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Common\Constants\System;
use App\Domain\Pvp\UseCases\PvpAbortUseCase;
use App\Domain\Pvp\UseCases\PvpChangeOpponentUseCase;
use App\Domain\Pvp\UseCases\PvpCleanupUseCase;
use App\Domain\Pvp\UseCases\PvpEndUseCase;
use App\Domain\Pvp\UseCases\PvpRankingUseCase;
use App\Domain\Pvp\UseCases\PvpResumeUseCase;
use App\Domain\Pvp\UseCases\PvpStartUseCase;
use App\Domain\Pvp\UseCases\PvpTopUseCase;
use App\Http\Requests\Api\Pvp\EndRequest;
use App\Http\ResponseFactories\PvpResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PvpController extends Controller
{
    public function __construct(
        private Request $request,
        private PvpResponseFactory $responseFactory,
    ) {
    }

    public function top(PvpTopUseCase $useCase): JsonResponse
    {
        $resultData = $useCase->exec(
            $this->request->user(),
        );

        return $this->responseFactory->createPvpTopResponse($resultData);
    }

    public function changeOpponent(PvpChangeOpponentUseCase $useCase): JsonResponse
    {
        $resultData = $useCase->exec($this->request->user());
        return $this->responseFactory->createPvpChangeOpponentResponse($resultData);
    }

    public function start(PvpStartUseCase $useCase): JsonResponse
    {
        $validated = $this->request->validate([
            'sysPvpSeasonId' => 'required|string',
            'isUseItem' => 'required|integer|in:0,1',
            'opponentMyId' => 'required|string',
            'partyNo' => 'required|integer',
            'inGameBattleLog' => 'required|array',
        ]);

        $resultData = $useCase->exec(
            $this->request->user(),
            $validated['sysPvpSeasonId'],
            (bool)$validated['isUseItem'],
            $validated['opponentMyId'],
            $validated['partyNo'],
            $validated['inGameBattleLog']
        );

        return $this->responseFactory->createPvpStartResponse($resultData);
    }

    public function end(PvpEndUseCase $useCase, EndRequest $request): JsonResponse
    {
        $platform = (int)$request->header(System::HEADER_PLATFORM);
        $resultData = $useCase->exec(
            $this->request->user(),
            $platform,
            $request->getSysPvpSeasonId(),
            $request->getInGameBattleLog(),
            $request->getIsWin()
        );

        return $this->responseFactory->createPvpEndResponse($resultData);
    }

    public function ranking(PvpRankingUseCase $useCase): JsonResponse
    {
        $validated = $this->request->validate([
            'isPreviousSeason' => 'required',
        ]);
        $resultData = $useCase->exec(
            $this->request->user(),
            filter_var($validated['isPreviousSeason'], FILTER_VALIDATE_BOOLEAN)
        );

        return $this->responseFactory->createPvpRankingResponse($resultData);
    }

    public function resume(PvpResumeUseCase $useCase): JsonResponse
    {
        $resultData = $useCase->exec($this->request->user());
        return $this->responseFactory->createPvpResumeResponse($resultData);
    }

    public function abort(PvpAbortUseCase $useCase): JsonResponse
    {
        $resultData = $useCase->exec($this->request->user());
        return $this->responseFactory->createPvpAbortResponse($resultData);
    }

    public function cleanup(PvpCleanupUseCase $useCase): JsonResponse
    {
        $resultData = $useCase->exec($this->request->user());
        return $this->responseFactory->createPvpCleanupResponse($resultData);
    }
}
