<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Common\Constants\System;
use App\Domain\Unit\UseCases\UnitGradeUpUseCase;
use App\Domain\Unit\UseCases\UnitLevelUpUseCase;
use App\Domain\Unit\UseCases\UnitRankUpUseCase;
use App\Domain\Unit\UseCases\UnitReceiveGradeUpRewardUseCase;
use App\Http\ResponseFactories\UnitResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function __construct(
        private Request $request,
        private UnitResponseFactory $responseFactory,
    ) {
    }

    public function gradeUp(UnitGradeUpUseCase $useCase): JsonResponse
    {
        $validated = $this->request->validate([
            'usrUnitId' => 'required',
        ]);
        $user = $this->request->user();
        $usrUnitId = $validated['usrUnitId'];
        $platform = (int) $this->request->header(System::HEADER_PLATFORM);
        $resultData = $useCase->exec($user, $usrUnitId, $platform);

        return $this->responseFactory->createUnitGradeUpResponse($resultData);
    }

    public function levelUp(UnitLevelUpUseCase $useCase): JsonResponse
    {
        $validated = $this->request->validate([
            'usrUnitId' => 'required',
            'level' => 'required',
        ]);
        $user = $this->request->user();
        $usrUnitId = $validated['usrUnitId'];
        $level = $validated['level'];
        $resultData = $useCase->exec($user, $usrUnitId, $level);

        return $this->responseFactory->createUnitLevelUpResponse($resultData);
    }

    public function rankUp(UnitRankUpUseCase $useCase): JsonResponse
    {
        $validated = $this->request->validate([
            'usrUnitId' => 'required',
        ]);
        $user = $this->request->user();
        $usrUnitId = $validated['usrUnitId'];
        $resultData = $useCase->exec($user, $usrUnitId);

        return $this->responseFactory->createRankUpResponse($resultData);
    }

    public function receiveGradeUpReward(UnitReceiveGradeUpRewardUseCase $useCase): JsonResponse
    {
        $validated = $this->request->validate([
            'usrUnitId' => 'required',
        ]);
        $user = $this->request->user();
        $usrUnitId = $validated['usrUnitId'];
        $platform = (int) $this->request->header(System::HEADER_PLATFORM);
        $resultData = $useCase->exec($user->id, $usrUnitId, $platform);

        return $this->responseFactory->createReceiveGradeUpRewardResponse($resultData);
    }
}
