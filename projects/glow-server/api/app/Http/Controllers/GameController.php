<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Common\Constants\System;
use App\Domain\Game\UseCases\GameBadgeUseCase;
use App\Domain\Game\UseCases\GameFetchUseCase;
use App\Domain\Game\UseCases\GameServerTimeUseCase;
use App\Domain\Game\UseCases\GameUpdateAndFetchUseCase;
use App\Domain\Game\UseCases\GameVersionUseCase;
use App\Http\ResponseFactories\GameResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function __construct(
        private Request $request,
        private GameResponseFactory $responseFactory,
    ) {
    }

    public function version(GameVersionUseCase $useCase): JsonResponse
    {
        $language = $this->request->header(System::HEADER_LANGUAGE);
        $platform = (int) $this->request->header(System::HEADER_PLATFORM);
        $clientVersion = $this->request->header(System::CLIENT_VERSION);

        // Asset-Versionリクエストヘッダは廃止になったようなので、null指定
        // @see https://wonderplanet.atlassian.net/wiki/spaces/wonderplanet/pages/216860649/SEED
        $resultData = $useCase->exec($this->request->user(), $language, $platform, $clientVersion);

        return $this->responseFactory->createVersionResponse($resultData);
    }

    public function updateAndFetch(GameUpdateAndFetchUseCase $useCase): JsonResponse
    {
        $language = $this->request->header(System::HEADER_LANGUAGE);
        $platform = (int) $this->request->header(System::HEADER_PLATFORM);
        $accessToken = $this->request->header(System::HEADER_ACCESS_TOKEN, '');
        $adId = $this->request->header('X-Ad-ID');

        $validated = $this->request->validate([
            'countryCode' => 'sometimes|nullable|string',
        ]);

        $countryCode = $validated['countryCode'] ?? null;

        $resultData = $useCase->exec(
            $this->request->user(),
            $language,
            $platform,
            $accessToken,
            $countryCode,
            $adId
        );

        return $this->responseFactory->createUpdateAndFetchResponse($resultData);
    }

    public function fetch(GameFetchUseCase $useCase): JsonResponse
    {
        $language = $this->request->header(System::HEADER_LANGUAGE);

        $resultData = $useCase->exec($this->request->user(), $language);

        return $this->responseFactory->createFetchResponse($resultData);
    }

    public function serverTime(GameServerTimeUseCase $useCase): JsonResponse
    {
        $resultData = $useCase($this->request->user());

        return $this->responseFactory->createServerTimeResponse($resultData);
    }

    public function badge(GameBadgeUseCase $useCase): JsonResponse
    {
        $language = $this->request->header(System::HEADER_LANGUAGE);

        $resultData = $useCase->exec($this->request->user(), $language);

        return $this->responseFactory->createBadgeResponse($resultData);
    }
}
