<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Common\Constants\System;
use App\Domain\Tutorial\UseCases\TutorialGachaConfirmUseCase;
use App\Domain\Tutorial\UseCases\TutorialGachaDrawUseCase;
use App\Domain\Tutorial\UseCases\TutorialStageEndUseCase;
use App\Domain\Tutorial\UseCases\TutorialStageStartUseCase;
use App\Domain\Tutorial\UseCases\TutorialUnitLevelUpUseCase;
use App\Domain\Tutorial\UseCases\TutorialUpdateStatusUseCase;
use App\Http\ResponseFactories\TutorialResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TutorialController extends Controller
{
    public function __construct(
        private TutorialResponseFactory $responseFactory,
    ) {
    }

    public function updateStatus(TutorialUpdateStatusUseCase $useCase, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mstTutorialFunctionName' => 'required',
        ]);

        $resultData = $useCase->exec(
            $request->user(),
            $validated['mstTutorialFunctionName'],
            (int) $request->header(System::HEADER_PLATFORM)
        );

        return $this->responseFactory->createUpdateStatusResponse($resultData);
    }

    public function gachaDraw(TutorialGachaDrawUseCase $useCase, Request $request): JsonResponse
    {
        $resultData = $useCase->exec(
            $request->user(),
        );

        return $this->responseFactory->createGachaDrawResponse($resultData);
    }

    public function gachaConfirm(TutorialGachaConfirmUseCase $useCase, Request $request): JsonResponse
    {
        $resultData = $useCase->exec(
            $request->user(),
            (int) $request->header(System::HEADER_PLATFORM),
        );

        return $this->responseFactory->createGachaConfirmResponse($resultData);
    }

    public function stageStart(TutorialStageStartUseCase $useCase, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mstTutorialFunctionName' => 'required',
            'partyNo' => 'required',
        ]);

        $resultData = $useCase->exec(
            $request->user(),
            $validated['mstTutorialFunctionName'],
            $validated['partyNo'],
            (int) $request->header(System::HEADER_PLATFORM),
        );

        return $this->responseFactory->createStageStartResponse($resultData);
    }

    public function stageEnd(TutorialStageEndUseCase $useCase, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mstTutorialFunctionName' => 'required',
        ]);

        $resultData = $useCase->exec(
            $request->user(),
            $validated['mstTutorialFunctionName'],
            (int) $request->header(System::HEADER_PLATFORM),
        );

        return $this->responseFactory->createStageEndResponse($resultData);
    }

    public function unitLevelUp(TutorialUnitLevelUpUseCase $useCase, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mstTutorialFunctionName' => 'required',
            'usrUnitId' => 'required',
            'level' => 'required|integer',
        ]);

        $resultData = $useCase->exec(
            $request->user(),
            $validated['mstTutorialFunctionName'],
            $validated['usrUnitId'],
            $validated['level'],
            (int) $request->header(System::HEADER_PLATFORM),
        );

        return $this->responseFactory->createUnitLevelUpResponse($resultData);
    }
}
