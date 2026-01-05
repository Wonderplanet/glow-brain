<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Common\Constants\System;
use App\Domain\User\UseCases\UserAgreeUseCase;
use App\Domain\User\UseCases\UserBuyStaminaAdUseCase;
use App\Domain\User\UseCases\UserBuyStaminaDiamondUseCase;
use App\Domain\User\UseCases\UserChangeAvatarUseCase;
use App\Domain\User\UseCases\UserChangeEmblemUseCase;
use App\Domain\User\UseCases\UserChangeNameUseCase;
use App\Domain\User\UseCases\UserInfoUseCase;
use App\Domain\User\UseCases\UserLinkBnidConfirmUseCase;
use App\Domain\User\UseCases\UserLinkBnidUseCase;
use App\Domain\User\UseCases\UserUnlinkBnidUseCase;
use App\Http\ResponseFactories\UserResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private Request $request,
        private UserResponseFactory $responseFactory,
    ) {
    }

    public function info(UserInfoUseCase $useCase, Request $request): JsonResponse
    {
        $resultData = $useCase->exec($request->user());

        return $this->responseFactory->createInfoResponse($resultData);
    }

    public function changeName(UserChangeNameUseCase $useCase, Request $request): JsonResponse
    {
        $validated = $this->request->validate([
            'name' => 'required',
        ]);
        $user = $this->request->user();
        $newName = $validated['name'];

        $useCase->exec($user, $newName);

        return response()->json();
    }

    public function changeAvatar(UserChangeAvatarUseCase $useCase): JsonResponse
    {
        $validated = $this->request->validate([
            'mstUnitId' => 'required',
        ]);
        $user = $this->request->user();
        $mstUnitId = $validated['mstUnitId'];
        $resultData = $useCase->exec($user, $mstUnitId);

        return $this->responseFactory->createUserChangeAvatarResponse($resultData);
    }

    public function changeEmblem(UserChangeEmblemUseCase $useCase): JsonResponse
    {
        $validated = $this->request->validate([
            'mstEmblemId' => 'present',
        ]);
        $user = $this->request->user();
        $mstEmblemId = $validated['mstEmblemId'];
        $resultData = $useCase->exec($user, $mstEmblemId);

        return $this->responseFactory->createUserChangeEmblemResponse($resultData);
    }

    public function buyStaminaAd(UserBuyStaminaAdUseCase $useCase): JsonResponse
    {
        $user = $this->request->user();

        $resultData = $useCase->exec($user);

        return $this->responseFactory->createBuyStaminaAdResponse($resultData);
    }

    public function buyStaminaDiamond(UserBuyStaminaDiamondUseCase $useCase, Request $request): JsonResponse
    {
        $user = $this->request->user();
        $platform = (int) $this->request->header(System::HEADER_PLATFORM);
        $billingPlatform = $request->getBillingPlatform();

        $resultData = $useCase->exec($user, $platform, $billingPlatform);

        return $this->responseFactory->createBuyStaminaDiamondResponse($resultData);
    }

    public function linkBnidConfirm(UserLinkBnidConfirmUseCase $useCase): JsonResponse
    {
        $validated = $this->request->validate([
            'code' => 'required',
        ]);
        $user = $this->request->user();
        $code = $validated['code'];
        $ip = $this->request->ip();

        $resultData = $useCase->exec($user, $code, $ip);

        return $this->responseFactory->createLinkBnidConfirmResponse($resultData);
    }

    public function linkBnid(UserLinkBnidUseCase $useCase, Request $request): JsonResponse
    {
        $validated = $this->request->validate([
            'code' => 'required',
            'isHome' => 'required|boolean',
        ]);
        $user = $this->request->user();
        $platform = $request->getPlatform();
        $code = $validated['code'];
        $isHome = $validated['isHome'] ?? false;
        $accessToken = $request->header(System::HEADER_ACCESS_TOKEN);
        $ip = $request->ip();

        $resultData = $useCase->exec($user, $platform, $code, $isHome, $accessToken, $ip);

        return $this->responseFactory->createLinkBnidResponse($resultData);
    }

    public function unlinkBnid(UserUnlinkBnidUseCase $useCase, Request $request): JsonResponse
    {
        $user = $this->request->user();
        $accessToken = $request->header(System::HEADER_ACCESS_TOKEN);
        $platform = $request->getPlatform();

        $useCase->exec($user, $accessToken, $platform);

        return response()->json();
    }

    public function agree(UserAgreeUseCase $useCase, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tosVersion' => 'required|integer',
            'privacyPolicyVersion' => 'required|integer',
            'globalCnsntVersion' => 'required|integer',
            'inAppAdvertisementVersion' => 'required|integer',
        ]);

        $user = $request->user();
        $tosVersion = $validated['tosVersion'];
        $privacyPolicyVersion = $validated['privacyPolicyVersion'];
        $globalCnsntVersion = $validated['globalCnsntVersion'];
        $iaaVersion = $validated['inAppAdvertisementVersion'];
        $language = $request->header(System::HEADER_LANGUAGE);

        $useCase->exec($user, $tosVersion, $privacyPolicyVersion, $globalCnsntVersion, $iaaVersion, $language);

        return response()->json();
    }
}
