<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Common\Constants\System;
use App\Domain\Item\UseCases\ItemConsumeUseCase;
use App\Domain\Item\UseCases\ItemExchangeSelectItemUseCase;
use App\Http\ResponseFactories\ItemResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function __construct(
        private Request $request,
    ) {
    }

    public function consume(ItemConsumeUseCase $useCase, ItemResponseFactory $responseFactory): JsonResponse
    {
        $user = $this->request->user();
        $platform = (int) $this->request->header(System::HEADER_PLATFORM);
        $validated = $this->request->validate([
            'mstItemId' => 'required',
            'amount' => 'required',
        ]);
        $mstItemId = $validated['mstItemId'];
        $amount = $validated['amount'];
        $response = $useCase->exec($user, $platform, $mstItemId, $amount);

        return $responseFactory->createItemConsumeResponse($response);
    }

    public function exchangeSelectItem(
        ItemExchangeSelectItemUseCase $useCase,
        ItemResponseFactory $responseFactory
    ): JsonResponse {
        $user = $this->request->user();
        $platform = (int) $this->request->header(System::HEADER_PLATFORM);
        $validated = $this->request->validate([
            'mstItemId' => 'required',
            'selectMstItemId' => 'required',
            'amount' => 'required',
        ]);
        $mstItemId = $validated['mstItemId'];
        $selectMstItemId = $validated['selectMstItemId'];
        $amount = $validated['amount'];
        $response = $useCase->exec($user, $platform, $mstItemId, $selectMstItemId, $amount);

        return $responseFactory->createItemExchangeSelectItemResponse($response);
    }
}
