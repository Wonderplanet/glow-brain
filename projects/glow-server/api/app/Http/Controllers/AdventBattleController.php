<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\AdventBattle\UseCases\AdventBattleAbortUseCase;
use App\Domain\AdventBattle\UseCases\AdventBattleCleanupUseCase;
use App\Domain\AdventBattle\UseCases\AdventBattleEndUseCase;
use App\Domain\AdventBattle\UseCases\AdventBattleInfoUseCase;
use App\Domain\AdventBattle\UseCases\AdventBattleRankingUseCase;
use App\Domain\AdventBattle\UseCases\AdventBattleStartUseCase;
use App\Domain\AdventBattle\UseCases\AdventBattleTopUseCase;
use App\Domain\Common\Constants\System;
use App\Http\Requests\Api\AdventBattle\AbortRequest;
use App\Http\Requests\Api\AdventBattle\EndRequest;
use App\Http\Requests\Api\AdventBattle\StartRequest;
use App\Http\ResponseFactories\AdventBattleResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdventBattleController extends Controller
{
    public function __construct(
        private readonly Request $request,
        private readonly AdventBattleResponseFactory $responseFactory,
    ) {
    }

    public function top(
        AdventBattleTopUseCase $useCase,
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'mstAdventBattleId' => 'required',
        ]);
        $mstAdventBattleId = $validated['mstAdventBattleId'];
        $platform = (int)$this->request->header(System::HEADER_PLATFORM);

        $resultData = $useCase->exec($this->request->user(), $mstAdventBattleId, $platform);

        return $this->responseFactory->createTopResponse($resultData);
    }

    public function start(AdventBattleStartUseCase $useCase, StartRequest $request): JsonResponse
    {
        $resultData = $useCase->exec(
            $this->request->user(),
            $request->getMstAdventBattleId(),
            $request->getPartyNo(),
            $request->getIsChallengeAd(),
            $request->getInGameBattleLog(),
        );

        return $this->responseFactory->createStartResponse($resultData);
    }

    public function end(AdventBattleEndUseCase $useCase, EndRequest $request): JsonResponse
    {
        $resultData = $useCase->exec(
            $this->request->user(),
            $request->getMstAdventBattleId(),
            (int)$this->request->header(System::HEADER_PLATFORM),
            $request->getInGameBattleLog(),
        );

        return $this->responseFactory->createEndResponse($resultData);
    }

    public function abort(AdventBattleAbortUseCase $useCase, AbortRequest $request): JsonResponse
    {
        $resultData = $useCase->exec(
            $this->request->user(),
            $request->getAbortType(),
        );

        return $this->responseFactory->createAbortResponse($resultData);
    }

    public function ranking(AdventBattleRankingUseCase $useCase): JsonResponse
    {
        $validated = $this->request->validate([
            'mstAdventBattleId' => 'required',
        ]);
        $isPrevious = $validated['isPrevious'] ?? false;
        $resultData = $useCase->exec(
            $this->request->user(),
            $validated['mstAdventBattleId'],
            filter_var($isPrevious, FILTER_VALIDATE_BOOLEAN)
        );

        return $this->responseFactory->createRankingResponse($resultData);
    }

    public function info(AdventBattleInfoUseCase $useCase): JsonResponse
    {
        $resultData = $useCase->exec($this->request->user());

        return $this->responseFactory->createInfoResponse($resultData);
    }

    public function cleanup(AdventBattleCleanupUseCase $useCase): JsonResponse
    {
        $resultData = $useCase->exec($this->request->user());

        return $this->responseFactory->createCleanupResponse($resultData);
    }
}
