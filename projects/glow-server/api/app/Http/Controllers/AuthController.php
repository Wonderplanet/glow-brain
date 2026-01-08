<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Auth\UseCases\SignInUseCase;
use App\Domain\Auth\UseCases\SignUpUseCase;
use App\Http\ResponseFactories\AuthResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthResponseFactory $responseFactory,
    ) {
    }

    public function signUp(SignUpUseCase $useCase, Request $request): JsonResponse
    {
        $platform = $request->getPlatform();
        $billingPlatform = $request->getBillingPlatform();
        $clientUuid = $request->input('clientUuid');

        $data = $useCase->exec($platform, $billingPlatform, $clientUuid);

        return $this->responseFactory->createSignUpResponse($request->getBillingPlatform(), $data);
    }

    public function signIn(SignInUseCase $useCase, Request $request): JsonResponse
    {
        $validated = $request->validate(['id_token' => 'required']);

        $data = $useCase($validated['id_token']);

        return response()->json($data);
    }
}
