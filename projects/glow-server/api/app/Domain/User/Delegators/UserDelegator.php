<?php

declare(strict_types=1);

namespace App\Domain\User\Delegators;

use App\Domain\Resource\Entities\LogTriggers\LogTrigger;
use App\Domain\Resource\Mst\Entities\MstTutorialEntity;
use App\Domain\Resource\Usr\Entities\UsrUserEntity;
use App\Domain\Resource\Usr\Entities\UsrUserLoginEntity;
use App\Domain\Resource\Usr\Entities\UsrUserParameterEntity;
use App\Domain\Resource\Usr\Entities\UsrUserProfileEntity;
use App\Domain\User\Repositories\UsrOsPlatformPublicRepository;
use App\Domain\User\Repositories\UsrOsPlatformRepository;
use App\Domain\User\Repositories\UsrUserLoginRepository;
use App\Domain\User\Repositories\UsrUserParameterRepository;
use App\Domain\User\Repositories\UsrUserProfileRepository;
use App\Domain\User\Repositories\UsrUserRepository;
use App\Domain\User\Services\UserBuyStaminaService;
use App\Domain\User\Services\UserLoginCountService;
use App\Domain\User\Services\UserService;
use App\Domain\User\Services\UserTutorialService;
use App\Http\Responses\Data\UserLevelUpData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UserDelegator
{
    public function __construct(
        // Repository
        private UsrOsPlatformPublicRepository $usrOsPlatformPublicRepository,
        private UsrOsPlatformRepository $usrOsPlatformRepository,
        private UsrUserRepository $usrUserRepository,
        private UsrUserProfileRepository $usrUserProfileService,
        private UsrUserLoginRepository $usrUserLoginRepository,
        private UsrUserParameterRepository $usrUserParameterRepository,
        // Service
        private UserService $userService,
        private UserBuyStaminaService $userBuyStaminaService,
        private UserLoginCountService $userLoginCountService,
        private UserTutorialService $userTutorialService,
    ) {
    }
    public function getUsrUserByUsrUserId(string $usrUserId): UsrUserEntity
    {
        return $this->usrUserRepository->findById($usrUserId)->toEntity();
    }

    public function getUsrUserParameterByUsrUserId(string $usrUserId): UsrUserParameterEntity
    {
        return $this->usrUserParameterRepository->findByUsrUserId($usrUserId)->toEntity();
    }

    public function getUsrUserParameterWithRecoveryStamina(
        string $usrUserId,
        CarbonImmutable $now,
    ): UsrUserParameterEntity {
        return $this->userService->recoveryStamina($usrUserId, $now)->toEntity();
    }

    public function createUsrUserProfile(string $usrUserId): UsrUserProfileEntity
    {
        return $this->userService->createUsrUserProfile($usrUserId)->toEntity();
    }

    public function getUsrUserProfileByUsrUserId(string $usrUserId): UsrUserProfileEntity
    {
        return $this->usrUserProfileService->findByUsrUserId($usrUserId)->toEntity();
    }

    /**
     * @return Collection<string, UsrUserProfileEntity> key: usr_users.id
     */
    public function getUsrUserProfilesByUsrUserIds(Collection $usrUserIds): Collection
    {
        $usrEntities = collect();
        $usrModels = $this->usrUserProfileService->findByUsrUserIds($usrUserIds);
        foreach ($usrModels as $usrModel) {
            $usrEntities->put($usrModel->getUsrUserId(), $usrModel->toEntity());
        }

        return $usrEntities;
    }

    public function getUsrUserLogin(string $usrUserId): ?UsrUserLoginEntity
    {
        return $this->usrUserLoginRepository->get($usrUserId)?->toEntity();
    }


    public function consumeStamina(
        string $usrUserId,
        int $stamina,
        CarbonImmutable $now,
        LogTrigger $logTrigger
    ): void {
        $this->userService->consumeStamina($usrUserId, $stamina, $now, $logTrigger);
    }

    public function validateStamina(string $usrUserId, int $stamina, CarbonImmutable $now): void
    {
        $this->userService->validateStamina($usrUserId, $stamina, $now);
    }

    public function addStaminaByRewards(string $usrUserId, Collection $rewards, CarbonImmutable $now): void
    {
        $this->userService->addStaminaByRewards($usrUserId, $rewards, $now);
    }

    /**
     * パーセント指定でスタミナを回復する
     *
     * @param string $usrUserId ユーザーID
     * @param CarbonImmutable $now 現在時刻
     * @param int $percent ユーザー上限に対する回復割合（%）
     * @param int $multiplier 使用個数
     * @return int 実際の回復量
     */
    public function recoverStaminaByPercent(
        string $usrUserId,
        CarbonImmutable $now,
        int $percent,
        int $multiplier,
    ): int {
        return $this->userBuyStaminaService->recoverStaminaByPercent(
            $usrUserId,
            $now,
            $percent,
            $multiplier,
        );
    }

    /**
     * 固定値指定でスタミナを回復する
     *
     * @param string $usrUserId ユーザーID
     * @param CarbonImmutable $now 現在時刻
     * @param int $fixedAmount 固定回復量
     * @param int $multiplier 使用個数
     * @return int 実際の回復量
     */
    public function recoverStaminaByFixed(
        string $usrUserId,
        CarbonImmutable $now,
        int $fixedAmount,
        int $multiplier,
    ): int {
        return $this->userBuyStaminaService->recoverStaminaByFixed(
            $usrUserId,
            $now,
            $fixedAmount,
            $multiplier,
        );
    }

    public function consumeCoin(string $usrUserId, int $coin, CarbonImmutable $now, LogTrigger $logTrigger): void
    {
        $this->userService->consumeCoin($usrUserId, $coin, $now, $logTrigger);
    }

    public function addExp(string $usrUserId, int $exp, CarbonImmutable $now): UserLevelUpData
    {
        return $this->userService->addExp($usrUserId, $exp, $now);
    }

    public function addCoinByRewards(string $usrUserId, Collection $rewards, CarbonImmutable $now): void
    {
        $this->userService->addCoinByRewards($usrUserId, $rewards, $now);
    }

    public function setBirthDate(string $usrUserId, int $birthDate, CarbonImmutable $now): void
    {
        $this->userService->setBirthDate($usrUserId, $birthDate, $now);
    }

    public function updateIntroAndMainPartStatus(
        string $usrUserId,
        CarbonImmutable $now,
        MstTutorialEntity $mstTutorial,
    ): bool {
        return $this->userTutorialService->updateIntroAndMainPartStatus($usrUserId, $now, $mstTutorial);
    }

    public function getTutorialStatus(string $usrUserId): string
    {
        return $this->userTutorialService->getTutorialStatus($usrUserId);
    }

    /**
     * ログインカウント増加に応じて必要となる処理をまとめたメソッド
     */
    public function incrementLoginCountAndProcessActions(
        string $usrUserId,
        int $platform,
        CarbonImmutable $now,
    ): void {
        $this->userLoginCountService->incrementLoginCountAndProcessActions($usrUserId, $platform, $now);
    }

    public function isNewOsPlatform(string $usrUserId, string $platform): bool
    {
        return $this->userService->isNewOsPlatform($usrUserId, $platform);
    }

    public function createUsrOsPlatform(string $usrUserId, string $platform): void
    {
        $this->usrOsPlatformRepository->create($usrUserId, $platform);
    }

    public function createUsrOsPlatformIfNotRegistered(string $usrUserId, string $platform): void
    {
        $this->userService->createUsrOsPlatformIfNotRegistered($usrUserId, $platform);
    }

    public function createUsrOsPlatformPublic(string $usrUserId, string $platform): void
    {
        $this->usrOsPlatformPublicRepository->create($usrUserId, $platform);
    }

    public function createUsrOsPlatformPublicIfEmpty(string $usrUserId): void
    {
        $this->userService->createUsrOsPlatformPublicIfEmpty($usrUserId);
    }
}
