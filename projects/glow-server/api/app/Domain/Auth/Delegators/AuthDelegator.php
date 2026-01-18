<?php

declare(strict_types=1);

namespace App\Domain\Auth\Delegators;

use App\Domain\Auth\Repositories\UsrDeviceRepository;
use App\Domain\Auth\Services\AccessTokenService;
use App\Domain\Auth\Services\IdTokenService;
use App\Domain\Auth\Services\UserDeviceService;
use App\Domain\Resource\Entities\AccessTokenUser;
use App\Domain\Resource\Usr\Entities\UsrDeviceEntity;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class AuthDelegator
{
    public function __construct(
        private AccessTokenService $accessTokenService,
        private IdTokenService $idTokenService,
        private UserDeviceService $userDeviceService,
        private UsrDeviceRepository $usrDeviceRepository,
    ) {
    }

    public function createUserDevice(
        string $usrUserId,
        ?string $uuid = null,
        ?string $bnidLinkedAt = null,
        string $osPlatform = ''
    ): UsrDeviceEntity {
        return $this->usrDeviceRepository->create($usrUserId, $uuid, $bnidLinkedAt, $osPlatform)->toEntity();
    }

    public function createIdToken(string $uuid): string
    {
        return $this->idTokenService->create($uuid);
    }

    public function linkBnidByAccessToken(string $accessToken, CarbonImmutable $now): string
    {
        return $this->userDeviceService->linkBnidByAccessToken($accessToken, $now);
    }

    public function findUserByAccessToken(string $accessToken): ?AccessTokenUser
    {
        return $this->accessTokenService->findUser($accessToken);
    }

    public function findUsrDevice(string $usrDeviceId): UsrDeviceEntity
    {
        return $this->usrDeviceRepository->findById($usrDeviceId)->toEntity();
    }

    public function getUsrDevices(string $usrUserId): Collection
    {
        return $this->usrDeviceRepository
            ->getByUsrUserId($usrUserId)
            ->map(fn($record) => $record->toEntity());
    }

    public function findUsrDevicesByUsrUserIdAndOsPlatform(string $usrUserId, string $osPlatform): Collection
    {
        return $this->usrDeviceRepository
            ->findByUsrUserIdAndOsPlatform($usrUserId, $osPlatform)
            ->map(fn($record) => $record->toEntity());
    }

    public function findUsrDeviceByAccessToken(string $accessToken): ?UsrDeviceEntity
    {
        return $this->userDeviceService->findByAccessToken($accessToken)?->toEntity();
    }

    public function deleteUsrDevice(string $usrDeviceId, string $usrUserId): void
    {
        $this->usrDeviceRepository->deleteByIdAndUsrUserId($usrDeviceId, $usrUserId);
    }

    public function deleteAccessToken(string $usrUserId): void
    {
        $this->accessTokenService->delete($usrUserId);
    }
}
