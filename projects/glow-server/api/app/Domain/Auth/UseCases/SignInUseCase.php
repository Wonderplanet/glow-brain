<?php

declare(strict_types=1);

namespace App\Domain\Auth\UseCases;

use App\Domain\Auth\Services\AccessTokenService;
use App\Domain\Auth\Services\UserDeviceService;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;

class SignInUseCase
{
    public function __construct(
        private UserDeviceService $userDeviceService,
        private AccessTokenService $accessTokenService,
    ) {
    }

    /**
     * @return array{access_token: string}
     */
    public function __invoke(string $idToken): array
    {
        try {
            $userDevice = $this->userDeviceService->findByIdToken($idToken);
        } catch (\UnexpectedValueException $e) {
            throw new GameException(ErrorCode::INVALID_ID_TOKEN, $e->getMessage());
        }

        if ($userDevice === null) {
            throw new GameException(ErrorCode::USER_NOT_FOUND);
        }

        $userId = $userDevice->getUsrUserId();
        $accessToken = $this->accessTokenService->create($userId, $userDevice->getId());

        return ['access_token' => $accessToken];
    }
}
