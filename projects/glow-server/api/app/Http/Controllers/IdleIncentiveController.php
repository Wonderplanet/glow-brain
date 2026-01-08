<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Common\Constants\System;
use App\Domain\IdleIncentive\UseCases\IdleIncentiveQuickReceiveByAdUseCase;
use App\Domain\IdleIncentive\UseCases\IdleIncentiveQuickReceiveByDiamondUseCase;
use App\Domain\IdleIncentive\UseCases\IdleIncentiveReceiveUseCase;
use App\Http\ResponseFactories\IdleIncentiveResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IdleIncentiveController extends Controller
{
    public function __construct(
        private Request $request,
        private IdleIncentiveResponseFactory $responseFactory,
    ) {
    }

    public function receive(IdleIncentiveReceiveUseCase $useCase, Request $request): JsonResponse
    {
        $platform = (int) $this->request->header(System::HEADER_PLATFORM);

        $resultData = $useCase->exec($this->request->user(), $platform);

        return $this->responseFactory->createReceiveResponse($resultData);
    }

    public function quickReceiveByDiamond(
        IdleIncentiveQuickReceiveByDiamondUseCase $useCase,
        Request $request
    ): JsonResponse {
        $platform = (int) $this->request->header(System::HEADER_PLATFORM);
        $billingPlatform = $request->getBillingPlatform();

        $resultData = $useCase->exec($this->request->user(), $platform, $billingPlatform);

        return $this->responseFactory->createQuickReceiveByDiamondResponse($resultData);
    }

    public function quickReceiveByAd(IdleIncentiveQuickReceiveByAdUseCase $useCase, Request $request): JsonResponse
    {
        $platform = (int) $this->request->header(System::HEADER_PLATFORM);

        $resultData = $useCase->exec($this->request->user(), $platform);

        return $this->responseFactory->createQuickReceiveByAdResponse($resultData);
    }
}
