<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Common\Constants\System;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\UseCases\GachaDrawUseCase;
use App\Domain\Gacha\UseCases\GachaHistoryUseCase;
use App\Domain\Gacha\UseCases\GachaPrizeUseCase;
use App\Http\ResponseFactories\GachaResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GachaController extends Controller
{
    public function __construct(
        private Request $request,
        private GachaResponseFactory $responseFactory
    ) {
    }

    public function prize(GachaPrizeUseCase $useCase): JsonResponse
    {
        $validated = $this->request->validate([
            'oprGachaId' => 'required',
        ]);

        $resultData = $useCase->exec($validated['oprGachaId']);
        return $this->responseFactory->createPrizeResponse($resultData);
    }

    /**
     * @param GachaDrawUseCase $useCase
     *
     * @return JsonResponse
     */
    public function drawAd(GachaDrawUseCase $useCase): JsonResponse
    {
        $validated = $this->request->validate([
            'oprGachaId' => 'required',
            'drewCount' => 'required',
        ]);

        $resultData = $useCase->exec(
            $this->request->user(),
            $validated['oprGachaId'],
            $validated['drewCount'],
            1,
            null,
            1,
            (int)$this->request->header(System::HEADER_PLATFORM),
            $this->request->getBillingPlatform(),
            CostType::AD
        );
        return $this->responseFactory->createDrawResponse($resultData);
    }

    /**
     * @param GachaDrawUseCase $useCase
     *
     * @return JsonResponse
     */
    public function drawFree(GachaDrawUseCase $useCase): JsonResponse
    {
        $validated = $this->request->validate([
            'oprGachaId' => 'required',
            'drewCount' => 'required',
            'currentStepNumber' => 'nullable|integer',
        ]);

        $resultData = $useCase->exec(
            $this->request->user(),
            $validated['oprGachaId'],
            $validated['drewCount'],
            1,
            null,
            1,
            (int)$this->request->header(System::HEADER_PLATFORM),
            $this->request->getBillingPlatform(),
            CostType::FREE,
            $validated['currentStepNumber'] ?? null
        );
        return $this->responseFactory->createDrawResponse($resultData);
    }

    /**
     * @param GachaDrawUseCase $useCase
     *
     * @return JsonResponse
     */
    public function drawItem(GachaDrawUseCase $useCase): JsonResponse
    {
        $validated = $this->request->validate([
            'oprGachaId' => 'required',
            'drewCount' => 'required',
            'playNum' => 'required',
            'costId' => 'required',
            'costNum' => 'required',
            'currentStepNumber' => 'nullable|integer',
        ]);

        $resultData = $useCase->exec(
            $this->request->user(),
            $validated['oprGachaId'],
            $validated['drewCount'],
            $validated['playNum'],
            $validated['costId'],
            $validated['costNum'],
            (int)$this->request->header(System::HEADER_PLATFORM),
            $this->request->getBillingPlatform(),
            CostType::ITEM,
            $validated['currentStepNumber'] ?? null
        );
        return $this->responseFactory->createDrawResponse($resultData);
    }

    /**
     * @param GachaDrawUseCase $useCase
     *
     * @return JsonResponse
     */
    public function drawDiamond(GachaDrawUseCase $useCase): JsonResponse
    {
        $validated = $this->request->validate([
            'oprGachaId' => 'required',
            'drewCount' => 'required',
            'playNum' => 'required',
            'costNum' => 'required',
            'currentStepNumber' => 'nullable|integer',
        ]);

        $resultData = $useCase->exec(
            $this->request->user(),
            $validated['oprGachaId'],
            $validated['drewCount'],
            $validated['playNum'],
            null,
            $validated['costNum'],
            (int)$this->request->header(System::HEADER_PLATFORM),
            $this->request->getBillingPlatform(),
            CostType::DIAMOND,
            $validated['currentStepNumber'] ?? null
        );
        return $this->responseFactory->createDrawResponse($resultData);
    }

    /**
     * @param GachaDrawUseCase $useCase
     *
     * @return JsonResponse
     */
    public function drawPaidDiamond(GachaDrawUseCase $useCase): JsonResponse
    {
        $validated = $this->request->validate([
            'oprGachaId' => 'required',
            'drewCount' => 'required',
            'playNum' => 'required',
            'costNum' => 'required',
            'currentStepNumber' => 'nullable|integer',
        ]);

        $resultData = $useCase->exec(
            $this->request->user(),
            $validated['oprGachaId'],
            $validated['drewCount'],
            $validated['playNum'],
            null,
            $validated['costNum'],
            (int)$this->request->header(System::HEADER_PLATFORM),
            $this->request->getBillingPlatform(),
            CostType::PAID_DIAMOND,
            $validated['currentStepNumber'] ?? null
        );
        return $this->responseFactory->createDrawResponse($resultData);
    }

    public function history(GachaHistoryUseCase $useCase): JsonResponse
    {
        $resultData = $useCase->exec($this->request->user());
        return $this->responseFactory->createHistoryResponse($resultData);
    }
}
