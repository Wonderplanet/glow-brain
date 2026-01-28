<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Party\UseCases\ArtworkPartySaveUseCase;
use App\Domain\Party\UseCases\PartySaveUseCase;
use App\Http\ResponseFactories\PartyResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartyController extends Controller
{
    public function __construct(
        private Request $request,
        private PartyResponseFactory $responseFactory,
    ) {
    }

    public function save(PartySaveUseCase $useCase): JsonResponse
    {
        $validated = $this->request->validate([
            'parties' => 'required',
        ]);
        $resultData = $useCase->exec($this->request->user(), $validated['parties']);

        return $this->responseFactory->createPartySaveResponse($resultData);
    }

    public function artworkSave(ArtworkPartySaveUseCase $useCase): JsonResponse
    {
        $validated = $this->request->validate([
            'mstArtworkIds' => 'required|array',
            'mstArtworkIds.*' => 'string',
        ]);
        $resultData = $useCase->exec($this->request->user(), $validated['mstArtworkIds']);

        return $this->responseFactory->createArtworkPartySaveResponse($resultData);
    }
}
