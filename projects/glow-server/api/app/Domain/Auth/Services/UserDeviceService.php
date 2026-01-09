<?php

declare(strict_types=1);

namespace App\Domain\Auth\Services;

use App\Domain\Auth\Models\UsrDeviceInterface;
use App\Domain\Auth\Repositories\UsrDeviceRepository;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use Carbon\CarbonImmutable;

class UserDeviceService
{
    public function __construct(
        private AccessTokenService $accessTokenService,
        private IdTokenService $idTokenService,
        private UsrDeviceRepository $usrDeviceRepository,
    ) {
    }

    /**
     * @throws \UnexpectedValueException
     */
    public function findByIdToken(string $idToken): ?UsrDeviceInterface
    {
        $uuid = $this->idTokenService->getUuid($idToken);
        return $this->usrDeviceRepository->findByUuid($uuid);
    }

    /**
     * デバイスのBNID連携日時を更新する
     * @param string          $accessToken
     * @param CarbonImmutable $now
     * @return string
     * @throws GameException
     */
    public function linkBnidByAccessToken(string $accessToken, CarbonImmutable $now): string
    {
        /** @var \App\Domain\Resource\Entities\AccessTokenUser|null $accessTokenUser */
        $accessTokenUser = $this->accessTokenService->findUser($accessToken);
        if ($accessTokenUser === null) {
            throw new GameException(ErrorCode::INVALID_ACCESS_TOKEN);
        }
        $this->usrDeviceRepository->updateBnidLinkedAt(
            $accessTokenUser->getDeviceId(),
            $accessTokenUser->getUsrUserId(),
            $now
        );
        return $accessTokenUser->getDeviceId();
    }

    public function findByAccessToken(string $accessToken): ?UsrDeviceInterface
    {
        $accessTokenUser = $this->accessTokenService->findUser($accessToken);
        if ($accessTokenUser === null) {
            return null;
        }
        return $this->usrDeviceRepository->findById($accessTokenUser->getDeviceId());
    }
}
