<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Common\Constants\System;
use App\Domain\Exchange\UseCases\ExchangeTradeUseCase;
use App\Http\ResponseFactories\ExchangeResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExchangeController extends Controller
{
    public function __construct(
        private ExchangeResponseFactory $responseFactory,
    ) {
    }

    /**
     * 交換実行
     *
     * @param ExchangeTradeUseCase $useCase
     * @param Request $request
     * @return JsonResponse
     */
    public function trade(
        ExchangeTradeUseCase $useCase,
        Request $request
    ): JsonResponse {
        // バリデーション実行
        $validated = $request->validate([
            'mstExchangeId' => 'required|string',
            'mstExchangeLineupId' => 'required|string',
            'tradeCount' => 'required|integer|min:1',
        ]);

        $mstExchangeId = $validated['mstExchangeId'];
        $mstExchangeLineupId = $validated['mstExchangeLineupId'];
        $tradeCount = $validated['tradeCount'];
        $platform = (int) $request->header(System::HEADER_PLATFORM);

        $resultData = $useCase->exec(
            $request->user(),
            $mstExchangeId,
            $mstExchangeLineupId,
            $tradeCount,
            $platform,
        );

        return $this->responseFactory->createExchangeTradeResponse($resultData);
    }
}
