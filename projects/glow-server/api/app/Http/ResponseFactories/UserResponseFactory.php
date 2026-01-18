<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use App\Http\Responses\ResultData\UserBuyStaminaAdResultData;
use App\Http\Responses\ResultData\UserBuyStaminaDiamondResultData;
use App\Http\Responses\ResultData\UserChangeAvatarResultData;
use App\Http\Responses\ResultData\UserChangeEmblemResultData;
use App\Http\Responses\ResultData\UserChangeNameResultData;
use App\Http\Responses\ResultData\UserInfoResultData;
use App\Http\Responses\ResultData\UserLinkBnidConfirmResultData;
use App\Http\Responses\ResultData\UserLinkBnidResultData;
use Illuminate\Http\JsonResponse;

class UserResponseFactory
{
    use CurrencySummaryResponderTrait;

    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }

    public function createInfoResponse(UserInfoResultData $resultData): JsonResponse
    {
        $result = [];
        $result = $this->responseDataFactory->addMyIdData($result, $resultData->usrUserProfile);
        return response()->json($result);
    }

    public function createUserChangeNameResponse(UserChangeNameResultData $resultData): JsonResponse
    {
        $result = [];
        $usrUserProfile = $resultData->usrUserProfile;
        $result = $this->responseDataFactory->addUsrProfileData($result, $usrUserProfile);
        $usrParam = $resultData->usrUserParameter;
        $result = $this->responseDataFactory->addUsrParameterData($result, $usrParam);
        return response()->json($result);
    }

    public function createUserChangeAvatarResponse(UserChangeAvatarResultData $resultData): JsonResponse
    {
        $result = [];
        $usrUserProfile = $resultData->usrUserProfile;
        $result = $this->responseDataFactory->addUsrProfileData($result, $usrUserProfile);
        return response()->json($result);
    }

    public function createUserChangeEmblemResponse(UserChangeEmblemResultData $resultData): JsonResponse
    {
        $result = [];
        $usrUserProfile = $resultData->usrUserProfile;
        $result = $this->responseDataFactory->addUsrProfileData($result, $usrUserProfile);
        return response()->json($result);
    }

    public function createBuyStaminaAdResponse(UserBuyStaminaAdResultData $resultData): JsonResponse
    {
        $result = [];
        $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrUserParameter);
        $result = $this->responseDataFactory->addUsrBuyCountData($result, $resultData->usrUserBuyCount);

        return response()->json($result);
    }

    public function createBuyStaminaDiamondResponse(UserBuyStaminaDiamondResultData $resultData): JsonResponse
    {
        $result = [];
        $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrUserParameter);
        $result = $this->responseDataFactory->addUsrBuyCountData($result, $resultData->usrUserBuyCount);

        return response()->json($result);
    }

    public function createLinkBnidResponse(UserLinkBnidResultData $resultData): JsonResponse
    {
        return response()->json($resultData->linkBnidData->formatToResponse());
    }

    public function createLinkBnidConfirmResponse(UserLinkBnidConfirmResultData $resultData): JsonResponse
    {
        return response()->json($resultData->bnidLinkedUserData->formatToResponse());
    }
}
