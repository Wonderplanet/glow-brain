<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Outpost\UseCases\OutpostChangeArtworkUseCase;
use App\Domain\Outpost\UseCases\OutpostEnhanceUseCase;
use App\Http\ResponseFactories\OutpostResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OutpostController extends Controller
{
    public function __construct(
        private Request $request,
        private OutpostResponseFactory $responseFactory,
    ) {
    }

    public function enhance(OutpostEnhanceUseCase $useCase, Request $request): JsonResponse
    {
        $validated = $this->request->validate([
            'mstOutpostEnhancementId' => 'required',
            'level' => 'required',
        ]);

        $enhancementId = $validated['mstOutpostEnhancementId'];
        $level = $validated['level'];

        $resultData = $useCase->exec($this->request->user(), $enhancementId, $level);

        return $this->responseFactory->createEnhanceResponse($resultData);
    }

    public function changeArtwork(OutpostChangeArtworkUseCase $useCase, Request $request): JsonResponse
    {
        $validated = $this->request->validate([
            'mstOutpostId' => 'required',
            'mstArtworkId' => 'present',
        ]);

        $mstOutpostId = $validated['mstOutpostId'];
        $mstArtworkId = $validated['mstArtworkId'];

        $resultData = $useCase->exec($this->request->user(), $mstOutpostId, $mstArtworkId);

        return $this->responseFactory->createChangeArtworkResponse($resultData);
    }
}
