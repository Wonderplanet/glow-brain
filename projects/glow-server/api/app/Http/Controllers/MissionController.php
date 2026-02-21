<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Common\Constants\System;
use App\Domain\Mission\UseCases\MissionAdventBattleFetchUseCase;
use App\Domain\Mission\UseCases\MissionArtworkPanelUpdateAndFetchUseCase;
use App\Domain\Mission\UseCases\MissionBulkReceiveRewardUseCase;
use App\Domain\Mission\UseCases\MissionClearOnCallUseCase;
use App\Domain\Mission\UseCases\MissionEventDailyBonusUpdateUseCase;
use App\Domain\Mission\UseCases\MissionEventUpdateAndFetchUseCase;
use App\Domain\Mission\UseCases\MissionUpdateAndFetchUseCase;
use App\Http\ResponseFactories\MissionResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MissionController extends Controller
{
    public function __construct(
        private MissionResponseFactory $responseFactory,
    ) {
    }

    public function updateAndFetch(MissionUpdateAndFetchUseCase $useCase, Request $request): JsonResponse
    {
        $resultData = $useCase->exec($request->user());

        return $this->responseFactory->createUpdateAndFetchResponse($resultData);
    }

    public function bulkReceiveReward(MissionBulkReceiveRewardUseCase $useCase, Request $request): JsonResponse
    {
        $platform = (int) $request->header(System::HEADER_PLATFORM);

        $validated = $request->validate([
            'missionType' => 'required',
            'mstMissionIds' => 'required|array',
        ]);

        $missionType = $validated['missionType'];
        $mstMissionIds = $validated['mstMissionIds'];

        $resultData = $useCase->exec(
            $request->user(),
            platform: $platform,
            missionType: $missionType,
            mstMissionIds: $mstMissionIds,
        );

        return $this->responseFactory->createBulkReceiveRewardResponse($resultData);
    }

    public function clearOnCall(MissionClearOnCallUseCase $useCase, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'missionType' => 'required',
            'mstMissionId' => 'required',
        ]);

        $resultData = $useCase->exec(
            $request->user(),
            missionType: $validated['missionType'],
            mstMissionId: $validated['mstMissionId'],
        );

        return $this->responseFactory->createClearOnCallResponse($resultData);
    }

    public function eventDailyBonusUpdate(MissionEventDailyBonusUpdateUseCase $useCase, Request $request): JsonResponse
    {
        $platform = (int) $request->header(System::HEADER_PLATFORM);

        $resultData = $useCase->exec($request->user(), $platform);

        return $this->responseFactory->createEventDailyBonusUpdateResponse($resultData);
    }

    public function eventUpdateAndFetch(MissionEventUpdateAndFetchUseCase $useCase, Request $request): JsonResponse
    {
        $resultData = $useCase->exec($request->user());

        return $this->responseFactory->createEventUpdateAndFetchResponse($resultData);
    }

    public function adventBattleFetch(MissionAdventBattleFetchUseCase $useCase, Request $request): JsonResponse
    {
        $resultData = $useCase->exec($request->user());

        return $this->responseFactory->createAdventBattleFetchResponse($resultData);
    }

    public function artworkPanelUpdateAndFetch(
        MissionArtworkPanelUpdateAndFetchUseCase $useCase,
        Request $request
    ): JsonResponse {
        $resultData = $useCase->exec($request->user());

        return $this->responseFactory->createArtworkPanelUpdateAndFetchResponse($resultData);
    }
}
