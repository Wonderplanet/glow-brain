<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Common\Constants\System;
use App\Domain\Message\UseCases\MessageUpdateAndFetchUseCase;
use App\Domain\Message\UseCases\OpenUseCase;
use App\Domain\Message\UseCases\ReceiveUseCase;
use App\Http\ResponseFactories\MessageResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        private Request $request,
        private MessageResponseFactory $responseFactory,
    ) {
    }

    public function updateAndFetch(MessageUpdateAndFetchUseCase $useCase): JsonResponse
    {
        $language = $this->request->header(System::HEADER_LANGUAGE);

        $resultData = $useCase->exec($this->request->user(), $language);

        return $this->responseFactory->createUpdateAndFetchResponse($resultData);
    }

    public function open(OpenUseCase $useCase): JsonResponse
    {
        $validated = $this->request->validate([
            'usrMessageIds.*' => ['required', 'uuid'],
        ]);

        $resultData = $useCase->exec($this->request->user(), $validated['usrMessageIds']);

        return $this->responseFactory->createOpenedResponse($resultData);
    }

    public function receive(ReceiveUseCase $useCase): JsonResponse
    {
        $platform = (int) $this->request->header(System::HEADER_PLATFORM);
        $language = $this->request->header(System::HEADER_LANGUAGE);
        $validated = $this->request->validate([
            'usrMessageIds.*' => ['required', 'uuid'],
        ]);

        $resultData = $useCase->exec($this->request->user(), $platform, $validated['usrMessageIds'], $language);

        return $this->responseFactory->createReceivedResponse($resultData);
    }
}
