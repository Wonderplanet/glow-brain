<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Common\Constants\System;
use App\Domain\Encyclopedia\UseCases\ArtworkGradeUpUseCase;
use App\Domain\Encyclopedia\UseCases\EncyclopediaReceiveFirstCollectionRewardUseCase;
use App\Domain\Encyclopedia\UseCases\EncyclopediaReceiveRewardUseCase;
use App\Http\ResponseFactories\EncyclopediaResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EncyclopediaController extends Controller
{
    public function __construct(
        private readonly Request $request,
        private readonly EncyclopediaResponseFactory $responseFactory,
    ) {
    }

    public function receiveReward(EncyclopediaReceiveRewardUseCase $useCase): JsonResponse
    {
        $platform = (int) $this->request->header(System::HEADER_PLATFORM);

        $validated = $this->request->validate([
            'mstUnitEncyclopediaRewardIds' => 'required',
        ]);
        $mstUnitEncyclopediaRewardIds = collect((array)$validated['mstUnitEncyclopediaRewardIds']);

        $resultData = $useCase->exec($this->request->user(), $mstUnitEncyclopediaRewardIds, $platform);

        return $this->responseFactory->createReceiveRewardResponse($resultData);
    }

    /**
     * 新着の図鑑コンテンツを閲覧した際に既読処理と無償プリズムの付与を行う
     * @param EncyclopediaReceiveFirstCollectionRewardUseCase $useCase
     * @return JsonResponse
     */
    public function receiveFirstCollectionReward(EncyclopediaReceiveFirstCollectionRewardUseCase $useCase): JsonResponse
    {
        $platform = (int) $this->request->header(System::HEADER_PLATFORM);

        $validated = $this->request->validate([
            'encyclopediaType' => 'required',
            'encyclopediaId' => 'required',
        ]);

        $resultData = $useCase->exec(
            $this->request->user(),
            $validated['encyclopediaType'],
            $validated['encyclopediaId'],
            $platform
        );
        return $this->responseFactory->createReceiveFirstCollectionRewardResponse($resultData);
    }

    /**
     * 原画のグレードアップを行う
     * @param ArtworkGradeUpUseCase $useCase
     * @return JsonResponse
     */
    public function artworkGradeUp(ArtworkGradeUpUseCase $useCase): JsonResponse
    {
        $validated = $this->request->validate([
            'mstArtworkId' => 'required|string',
        ]);

        $resultData = $useCase->exec(
            $this->request->user(),
            $validated['mstArtworkId'],
        );

        return $this->responseFactory->createArtworkGradeUpResponse($resultData);
    }
}
