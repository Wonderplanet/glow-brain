<?php

declare(strict_types=1);

namespace App\Domain\User\Services;

use App\Domain\Auth\Delegators\AuthDelegator;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Enums\UserStatus;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Utils\StringUtil;
use App\Domain\Emblem\Delegators\EmblemDelegator;
use App\Domain\Resource\Entities\LogTriggers\LogTrigger;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Enums\UnreceivedRewardReason;
use App\Domain\Resource\Log\Enums\LogResourceActionType;
use App\Domain\Resource\Mst\Repositories\MstUserLevelBonusRepository;
use App\Domain\Resource\Mst\Repositories\MstUserLevelRepository;
use App\Domain\Resource\Mst\Services\MstConfigService;
use App\Domain\Resource\Mst\Services\MstNgWordService;
use App\Domain\Shop\Delegators\ShopPassEffectDelegator;
use App\Domain\Unit\Delegators\UnitDelegator;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameterInterface;
use App\Domain\User\Models\UsrUserProfileInterface;
use App\Domain\User\Repositories\LogCoinRepository;
use App\Domain\User\Repositories\LogStaminaRepository;
use App\Domain\User\Repositories\LogUserLevelRepository;
use App\Domain\User\Repositories\LogUserProfileRepository;
use App\Domain\User\Repositories\UsrOsPlatformPublicRepository;
use App\Domain\User\Repositories\UsrOsPlatformRepository;
use App\Domain\User\Repositories\UsrUserParameterRepository;
use App\Domain\User\Repositories\UsrUserProfileRepository;
use App\Domain\User\Repositories\UsrUserRepository;
use App\Http\Responses\Data\UserLevelUpData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UserService
{
    public function __construct(
        // Repository
        private MstUserLevelRepository $mstUserLevelRepository,
        private UsrUserParameterRepository $usrUserParameterRepository,
        private MstUserLevelBonusRepository $mstUserLevelBonusRepository,
        private UsrUserProfileRepository $usrUserProfileService,
        private LogCoinRepository $logCoinRepository,
        private LogStaminaRepository $logStaminaRepository,
        private LogUserLevelRepository $logUserLevelRepository,
        private LogUserProfileRepository $logUserProfileRepository,
        private UsrOsPlatformPublicRepository $usrOsPlatformPublicRepository,
        private UsrOsPlatformRepository $usrOsPlatformRepository,
        private UsrUserRepository $usrUserRepository,
        // Service
        private UserMissionTriggerService $userMissionTriggerService,
        private MstNgWordService $mstNgWordService,
        private MstConfigService $mstConfigService,
        // Delegator
        private EmblemDelegator $emblemDelegator,
        private UnitDelegator $unitDelegator,
        private ShopPassEffectDelegator $shopPassEffectDelegator,
        private AuthDelegator $authDelegator,
    ) {
    }

    /**
     * 必要な量のスタミナを持っているかチェックする
     * @throws GameException
     */
    public function validateStamina(string $usrUserId, int $stamina, CarbonImmutable $now): void
    {
        if ($this->getRecoveredStamina($usrUserId, $now) < $stamina) {
            throw new GameException(ErrorCode::LACK_OF_RESOURCES);
        }
    }

    /**
     * 現在のスタミナに自然回復を反映したスタミナを取得する
     * @param string $usrUserId
     * @param CarbonImmutable $now
     * @return int
     * @throws GameException
     */
    private function getRecoveredStamina(string $usrUserId, CarbonImmutable $now): int
    {
        $usrUserParameter = $this->usrUserParameterRepository->findByUsrUserId($usrUserId);
        $shopPassActiveEffect = $this->shopPassEffectDelegator
            ->getShopPassActiveEffectDataByUsrUserId($usrUserId, $now);
        $maxStamina = $this
            ->mstUserLevelRepository
            ->getByLevel($usrUserParameter->getLevel(), true)
            ->getStamina() + $shopPassActiveEffect->getStaminaAddRecoveryLimit();
        $stamina = $usrUserParameter->getStamina();
        if ($maxStamina <= $stamina) {
            // スタミナが最大値の場合は最大を今の値とする
            return min($stamina, $this->mstConfigService->getUserStaminaMaxAmount());
        } else {
            $recoveryStaminaMinute = $this->mstConfigService->getRecoveryStaminaMinute();
            $staminaUpdatedAt = new CarbonImmutable($usrUserParameter->getStaminaUpdatedAt());
            $diffMinutes = (int) $staminaUpdatedAt->diffInMinutes($now, true);
            $recoveryStamina = (int) ($diffMinutes / $recoveryStaminaMinute + $stamina);
            return min($recoveryStamina, $maxStamina);
        }
    }

    /**
     * スタミナ自然回復適用後のスタミナの更新日時を計算する
     * @param string $usrUserId
     * @param CarbonImmutable $now
     * @return string
     * @throws GameException
     */
    private function calcStaminaUpdatedAt(string $usrUserId, CarbonImmutable $now): string
    {
        $usrUserParameter = $this->usrUserParameterRepository->findByUsrUserId($usrUserId);
        $maxStamina = $this
            ->mstUserLevelRepository
            ->getByLevel($usrUserParameter->getLevel(), true)
            ->getStamina();
        $stamina = $usrUserParameter->getStamina();
        if ($maxStamina <= $stamina) {
            return $now->toDateTimeString();
        } else {
            $recoveryStaminaMinute = $this->mstConfigService->getRecoveryStaminaMinute();
            $staminaUpdatedAt = new CarbonImmutable($usrUserParameter->getStaminaUpdatedAt());
            $diffMinutes = (int) $staminaUpdatedAt->diffInMinutes($now, true);
            return $now
                ->subMinutes($diffMinutes % $recoveryStaminaMinute)
                ->toDateTimeString();
        }
    }

    /**
     * スタミナの時間回復する時間になっているか判定する
     * true: 回復分がある false:回復分がない
     */
    private function isRecoveryStamina(string $usrUserId, CarbonImmutable $now): bool
    {
        $usrUserParameter = $this->usrUserParameterRepository->findByUsrUserId($usrUserId);

        $staminaUpdatedAt = new CarbonImmutable($usrUserParameter->getStaminaUpdatedAt());
        $diffMinutes = (int) $staminaUpdatedAt->diffInMinutes($now, true);

        $recoveryStaminaMinute = $this->mstConfigService->getRecoveryStaminaMinute();

        return $diffMinutes >= $recoveryStaminaMinute;
    }

    /**
     * スタミナの自然回復を適用し適用後のUsrUserParameterInterfaceを返す
     * @param string $usrUserId
     * @param CarbonImmutable $now
     * @return UsrUserParameterInterface
     * @throws GameException
     */
    public function recoveryStamina(string $usrUserId, CarbonImmutable $now): UsrUserParameterInterface
    {
        if (!$this->isRecoveryStamina($usrUserId, $now)) {
            return $this->usrUserParameterRepository->findByUsrUserId($usrUserId);
        }

        $stamina = $this->getRecoveredStamina($usrUserId, $now);
        $updatedAt = $this->calcStaminaUpdatedAt($usrUserId, $now);

        $usrUserParameter = $this->usrUserParameterRepository->findByUsrUserId($usrUserId);
        $usrUserParameter->setStamina($stamina);
        $usrUserParameter->setStaminaUpdatedAt($updatedAt);

        $this->usrUserParameterRepository->syncModel($usrUserParameter);
        return $usrUserParameter;
    }

    public function addStamina(string $usrUserId, int $addStamina, CarbonImmutable $now): UsrUserParameterInterface
    {
        // 自然回復分を適用する
        $usrUserParameter = $this->recoveryStamina($usrUserId, $now);

        $maxAmount = $this->mstConfigService->getUserStaminaMaxAmount();
        $usrUserParameter->addStamina($addStamina, $maxAmount);
        $this->usrUserParameterRepository->syncModel($usrUserParameter);

        return $usrUserParameter;
    }

    public function consumeStamina(
        string $usrUserId,
        int $stamina,
        CarbonImmutable $now,
        LogTrigger $logTrigger,
    ): void {
        // 自然回復分を適用する
        $usrUserParameter = $this->recoveryStamina($usrUserId, $now);

        $beforeStamina = $usrUserParameter->getStamina();
        $usrUserParameter->subtractStamina($stamina);
        $this->usrUserParameterRepository->syncModel($usrUserParameter);

        // ログ送信
        $this->logStaminaRepository->create(
            $usrUserId,
            LogResourceActionType::USE,
            $beforeStamina,
            $usrUserParameter->getStamina(),
            $logTrigger->getLogTriggerData(),
        );
    }

    /**
     * コインを追加する
     *
     * @param string  $usrUserId
     * @param Collection $rewards
     * @param CarbonImmutable  $now
     * @throws GameException
     */
    public function addCoinByRewards(string $usrUserId, Collection $rewards, CarbonImmutable $now): void
    {
        $usrUserParameter = $this->usrUserParameterRepository->findByUsrUserId($usrUserId);
        // コインの最大値設定を取得
        $maxAmount = $this->mstConfigService->getUserCoinMaxAmount();
        // 更新後コイン数の初期値をいれる（現在数）
        $afterAmount = $usrUserParameter->getCoin();

        $totalAddedCoins = 0;
        foreach ($rewards as $reward) {
            /** @var BaseReward $reward */
            $addAmount = $reward->getAmount();
            // 現在コイン数を取得（コインリワードが複数あった場合に現在数の変動があるのでLOOPの中で処理）
            $beforeAmount = $usrUserParameter->getCoin();
            $reward->setBeforeAmount($beforeAmount);

            $afterAmount = $beforeAmount + $addAmount;

            if ($afterAmount > $maxAmount) {
                $reward->setUnreceivedRewardReason(UnreceivedRewardReason::RESOURCE_OVERFLOW_DISCARDED);
                $afterAmount = $maxAmount;
            }

            $reward->setAfterAmount($afterAmount);
            $usrUserParameter->setCoin($afterAmount);
            $reward->markAsSent();
            $totalAddedCoins += $addAmount;
        }

        $this->usrUserParameterRepository->syncModel($usrUserParameter);
        $this->userMissionTriggerService->sendCoinCollectTrigger($totalAddedCoins);
    }

    /**
     * スタミナを追加する
     *
     * @param string  $usrUserId
     * @param Collection $rewards
     * @param CarbonImmutable  $now
     * @throws GameException
     */
    public function addStaminaByRewards(string $usrUserId, Collection $rewards, CarbonImmutable $now): void
    {
        $usrUserParameter = $this->recoveryStamina($usrUserId, $now);
        // スタミナの最大値設定を取得
        $maxAmount = $this->mstConfigService->getUserStaminaMaxAmount();

        foreach ($rewards as $reward) {
            /** @var BaseReward $reward */
            $addAmount = $reward->getAmount();
            // 現在スタミナ数を取得（スタミナリワードが複数あった場合に現在数の変動があるのでLOOPの中で処理）
            $beforeAmount = $usrUserParameter->getStamina();
            $reward->setBeforeAmount($beforeAmount);

            $afterAmount = $beforeAmount + $addAmount;

            if ($afterAmount > $maxAmount) {
                $reward->setUnreceivedRewardReason(UnreceivedRewardReason::RESOURCE_OVERFLOW_DISCARDED);
                $afterAmount = $maxAmount;
            }

            $reward->setAfterAmount($afterAmount);
            $usrUserParameter->setStamina($afterAmount);
            $reward->markAsSent();
        }

        $this->usrUserParameterRepository->syncModel($usrUserParameter);
    }

    /**
     * コインを消費する
     * @throws GameException
     */
    public function consumeCoin(
        string $usrUserId,
        int $coin,
        CarbonImmutable $now,
        LogTrigger $logTrigger,
    ): void {
        $usrUserParameter = $this->usrUserParameterRepository->findByUsrUserId($usrUserId);

        if ($usrUserParameter->getCoin() < $coin) {
            throw new GameException(ErrorCode::LACK_OF_RESOURCES);
        }
        $beforeCoin = $usrUserParameter->getCoin();
        $usrUserParameter->subtractCoin($coin);
        $afterCoin = $usrUserParameter->getCoin();
        $this->usrUserParameterRepository->syncModel($usrUserParameter);

        // ミッショントリガー送信
        $this->userMissionTriggerService->sendCoinUsedTrigger($coin);

        // ログ送信
        $this->logCoinRepository->create(
            $usrUserId,
            LogResourceActionType::USE,
            $beforeCoin,
            $afterCoin,
            $logTrigger->getLogTriggerData(),
        );
    }

    /**
     * ユーザープロフィールデータを新規作成する
     * @param string $usrUserId
     * @return UsrUserProfileInterface
     */
    public function createUsrUserProfile(string $usrUserId): UsrUserProfileInterface
    {
        return $this->usrUserProfileService->create($usrUserId);
    }

    /**
     * ユーザー名を変更する
     *
     * @param string $usrUserId
     * @param string $newName
     * @return UsrUserProfileInterface
     * @throws GameException
     */
    public function setNewName(string $usrUserId, string $newName, CarbonImmutable $now): UsrUserProfileInterface
    {
        $usrProfile = $this->usrUserProfileService->findByUsrUserId($usrUserId);

        $beforeName = $usrProfile->getName();

        $nameUpdateAt = $now;
        $intervalHours = $this->mstConfigService->getUserNameChangeIntervalHours();

        /**
         * 名前変更2回までは、インターバルの時間を無視して変更できる
         * 3回目以降は、インターバルの時間が過ぎていることを確認する
         *
         * インターバル時間を無視する仕様について:
         * 1回目: チュートリアルの最初にインターバルなしで名前変更できるようにするため
         * 2回目: チュートリアルメインパート完了後に即時名前変更できるようにするため
         *   このために、一時的にname_update_atを過去に設定する
         */
        if ($usrProfile->isFirstNameChange()) {
            $nameUpdateAt = $now->subHours($intervalHours);
        } else {
            // 名前変更が3回目以降の場合は、インターバルの時間が過ぎていることを確認する
            // 過ぎていない場合は、名前変更できない
            $isPast = CarbonImmutable::parse($usrProfile->getNameUpdateAt())
                ->addHours($intervalHours)->isPast();

            // 指定時間経過していなければ名前変更できない
            if (!$isPast) {
                throw new GameException(ErrorCode::CHANGE_NAME_COOL_TIME);
            }
        }

        // 文字数超過
        if (mb_strlen($newName) > UserConstant::MAX_USER_NAME_LENGTH) {
            throw new GameException(ErrorCode::PLAYER_NAME_OVER_BYTE, 'user name is too long.');
        }
        // 全NGワードをチェック
        $this->mstNgWordService->validateNGWord($newName, ErrorCode::PLAYER_NAME_USED_NG_WORD);

        $usrProfile->setName($newName, $nameUpdateAt);
        $this->usrUserProfileService->syncModel($usrProfile);

        // ログ保存
        $this->logUserProfileRepository->create(
            $usrUserId,
            'name',
            $beforeName,
            $newName,
        );

        return $usrProfile;
    }

    /**
     * アバターを登録する
     * @param string $usrUserId
     * @param string $mstUnitId
     * @return UsrUserProfileInterface
     * @throws GameException
     */
    public function setNewAvatar(string $usrUserId, string $mstUnitId): UsrUserProfileInterface
    {
        $usrProfile = $this->usrUserProfileService->findByUsrUserId($usrUserId);

        $beforeMstUnitId = $usrProfile->getMstUnitId();

        // ユニットを所持していることを確認
        $this->unitDelegator->validateHasUsrUnitByMstUnitId($usrUserId, $mstUnitId);

        $usrProfile->setMstUnitId($mstUnitId);
        $this->usrUserProfileService->syncModel($usrProfile);

        // ログ保存
        $this->logUserProfileRepository->create(
            $usrUserId,
            'mst_unit_id',
            $beforeMstUnitId,
            $mstUnitId,
        );

        return $usrProfile;
    }

    /**
     * エンブレムを登録する
     * @param string $usrUserId
     * @param string|null $mstEmblemId
     * @return UsrUserProfileInterface
     * @throws GameException
     */
    public function setNewEmblem(string $usrUserId, ?string $mstEmblemId): UsrUserProfileInterface
    {
        $usrProfile = $this->usrUserProfileService->findByUsrUserId($usrUserId);

        $beforeMstEmblemId = $usrProfile->getMstEmblemId();

        // エンブレムが空文字の場合はエンブレムを外す状態にするため、そのままusrProfileにセット
        if (StringUtil::isSpecified($mstEmblemId)) {
            // エンブレムを所持していることを確認
            $this->emblemDelegator->validateHasUsrEmblem($usrUserId, $mstEmblemId);
        } else {
            $mstEmblemId = '';
        }
        $usrProfile->setMstEmblemId($mstEmblemId);
        $this->usrUserProfileService->syncModel($usrProfile);

        // ログ保存
        $this->logUserProfileRepository->create(
            $usrUserId,
            'mst_emblem_id',
            $beforeMstEmblemId,
            $mstEmblemId,
        );

        return $usrProfile;
    }

    /**
     * @param string $usrUserId
     * @param int $exp
     * @param CarbonImmutable $now
     * @return UserLevelUpData
     * @throws GameException
     */
    public function addExp(string $usrUserId, int $exp, CarbonImmutable $now): UserLevelUpData
    {
        $usrUserParameter = $this->recoveryStamina($usrUserId, $now);
        $beforeExp = $usrUserParameter->getExp();
        $currentLevel = $usrUserParameter->getLevel();

        // 現在レベル以上のデータを取得
        $mstLevels = $this->mstUserLevelRepository->getHigherLevelData($currentLevel);
        if ($mstLevels->count() === 1) {
            // すでにMAXレベルの場合はなにもしない
            return new UserLevelUpData($beforeExp, $beforeExp, collect());
        }

        // 経験値を加算
        $usrUserParameter->addExp($exp);
        $afterExp = $usrUserParameter->getExp();
        $afterLevel = $currentLevel;
        $maxAmount = $this->mstConfigService->getUserStaminaMaxAmount();
        foreach ($mstLevels as $mstLevel) {
            if ($afterExp >= $mstLevel->getExp()) {
                $afterLevel = $mstLevel->getLevel();
                if ($currentLevel < $afterLevel) {
                    $usrUserParameter->addStamina($mstLevel->getStamina(), $maxAmount);
                }
            } else {
                break;
            }
        }

        // 最大レベルになる場合は最大レベルの必要経験値に調整
        $maxLevel = $mstLevels->last()->getLevel();
        if ($maxLevel === $afterLevel) {
            $usrUserParameter->setExp($mstLevels->last()->getExp());
        }

        $usrUserParameter->setLevel($afterLevel);

        $this->usrUserParameterRepository->syncModel($usrUserParameter);
        // 到達分のレベルアップ報酬
        $userLevelUpRewards = $this->mstUserLevelBonusRepository->getBonuses($currentLevel, $afterLevel);

        // ミッショントリガー
        $this->userMissionTriggerService->sendUserLevelTrigger($currentLevel, $afterLevel);

        // ユーザーレベルログ保存
        $this->logUserLevelRepository->create(
            $usrUserId,
            $currentLevel,
            $afterLevel,
        );

        return new UserLevelUpData($beforeExp, $afterExp, $userLevelUpRewards);
    }

    /**
     * 生年月日を設定する
     *
     * @param string $usrUserId
     * @param int $birthDate
     * @param \Carbon\CarbonImmutable $now
     * @throws \App\Domain\Common\Exceptions\GameException
     * @return void
     */
    public function setBirthDate(string $usrUserId, int $birthDate, CarbonImmutable $now): void
    {
        $usrUserProfile = $this->usrUserProfileService->findByUsrUserId($usrUserId);

        $beforeBirthDate = $usrUserProfile->getBirthDate();
        if (!is_null($beforeBirthDate)) {
            throw new GameException(ErrorCode::USER_BIRTHDATE_ALREADY_REGISTERED);
        }

        if (!$this->isValidIntBirthDate($birthDate, $now)) {
            throw new GameException(
                ErrorCode::INVALID_PARAMETER,
                'Invalid birth date. Please check the format and range.'
            );
        }

        $usrUserProfile->setBirthDate($birthDate);
        $this->usrUserProfileService->syncModel($usrUserProfile);
    }

    /**
     * 生年月日の数値データが正しい形式かどうかを判定する
     *
     * @param int $birthDate
     * @return bool
     */
    public function isValidIntBirthDate(int $birthDate, CarbonImmutable $now): bool
    {
        // 文字列に変換して8桁であることを確認
        $birthDateString = (string) $birthDate;
        if (strlen($birthDateString) !== 8) {
            return false;
        }

        // 日付として正しいかチェック
        $year = (int) substr($birthDateString, 0, 4);
        $month = (int) substr($birthDateString, 4, 2);
        $day = (int) substr($birthDateString, 6, 2);
        if (!checkdate($month, $day, $year)) {
            return false;
        }

        // 未来の日付の場合はエラー
        if ($now->lt(CarbonImmutable::createFromDate($year, $month, $day))) {
            return false;
        }

        // 現実的でない年齢の場合はエラー
        $minYear = (int) $now->format('Y') - UserConstant::UNREGISTERABLE_AGE_MIN;
        if ($year < $minYear) {
            return false;
        }

        return true;
    }

    /**
     * 指定のOSプラットフォームが新規かどうかを判定する
     * @param string $usrUserId
     * @param string $osPlatform
     * @return bool
     */
    public function isNewOsPlatform(string $usrUserId, string $osPlatform): bool
    {
        // APIログインユーザーかどうかでリポジトリを切り替え
        if ($this->usrOsPlatformRepository->isOwnUsrUserId($usrUserId)) {
            $usrOsPlatform = $this->usrOsPlatformRepository->getByUsrUserIdAndOsPlatform($usrUserId, $osPlatform);
        } else {
            $usrOsPlatform = $this->usrOsPlatformPublicRepository->getByUsrUserIdAndOsPlatform($usrUserId, $osPlatform);
        }
        return is_null($usrOsPlatform);
    }

    /**
     * UsrOsPlatformが未登録の場合、指定のOSプラットフォーム情報でUsrOsPlatformを作成する
     *
     * @param string $usrUserId
     * @param string $platform
     * @return void
     */
    public function createUsrOsPlatformIfNotRegistered(string $usrUserId, string $platform): void
    {
        if ($this->isNewOsPlatform($usrUserId, $platform)) {
            $this->usrOsPlatformRepository->create($usrUserId, $platform);
        }
    }

    /**
     * UsrOsPlatformが存在しない場合、UsrDeviceからOSプラットフォーム情報を取得してUsrOsPlatformを作成する
     *
     * @param string $usrUserId
     * @return void
     */
    public function createUsrOsPlatformPublicIfEmpty(string $usrUserId): void
    {
        // log_banks.event_id=100重複対応でusr_os_platformsテーブルを導入しメンテナンス中にデータを登録したが、
        // マルチログイン解除ユーザー(log_bnid_links.action_type=Unlink)のみ登録しているので、
        // それ以外はusr_devicesテーブルの情報をもとに生成する

        // アカウント連携APIから呼ばれAPIログインユーザーではないユーザーに対してデータ取得を行うのでPublicRepositoryを使う
        $usrOsPlatforms = $this->usrOsPlatformPublicRepository->getByUsrUserId($usrUserId);
        if ($usrOsPlatforms->isNotEmpty()) {
            return ;
        }

        $platforms = $this->authDelegator->getUsrDevices($usrUserId)->map(function ($usrDevice) {
            return $usrDevice->getOsPlatform();
        })->unique();
        foreach ($platforms as $platform) {
            $this->usrOsPlatformPublicRepository->create($usrUserId, $platform);
        }
    }

    /**
     * ユーザーがBANされているかチェック
     * 通常はミドルウェアでチェックしているのでトークン認証できない外部決済のWebhook専用。
     *
     * @param string          $usrUserId ユーザーID
     * @param CarbonImmutable $now 現在日時
     * @return void
     * @throws GameException ユーザーがBANされている場合
     */
    public function checkUserBan(string $usrUserId, CarbonImmutable $now): void
    {
        $usrUser = $this->usrUserRepository->findById($usrUserId);
        $status = $usrUser->getStatus();

        switch ($status) {
            case UserStatus::BAN_TEMPORARY_CHEATING->value:
            case UserStatus::BAN_TEMPORARY_DETECTED_ANOMALY->value:
                $suspendEndAt = $usrUser->getSuspendEndAt();
                if ($suspendEndAt === null) {
                    // 時限BANの終了日時が未設定の場合は、利用停止中とみなす
                    throw new GameException(ErrorCode::USER_ACCOUNT_BAN_TEMPORARY_BY_CHEATING);
                }
                $suspendEndAtCarbon = CarbonImmutable::parse($suspendEndAt);
                if ($now->lte($suspendEndAtCarbon)) {
                    // 現在日時が終了日時以前の場合は利用停止中
                    if ($status === UserStatus::BAN_TEMPORARY_CHEATING->value) {
                        throw new GameException(ErrorCode::USER_ACCOUNT_BAN_TEMPORARY_BY_CHEATING);
                    } else {
                        throw new GameException(ErrorCode::USER_ACCOUNT_BAN_TEMPORARY_BY_DETECTED_ANOMALY);
                    }
                }
                break;
            case UserStatus::BAN_PERMANENT->value:
                throw new GameException(ErrorCode::USER_ACCOUNT_BAN_PERMANENT);
            case UserStatus::DELETED->value:
                throw new GameException(ErrorCode::USER_ACCOUNT_DELETED);
            case UserStatus::REFUNDING->value:
                throw new GameException(ErrorCode::USER_ACCOUNT_REFUNDING);
        }
    }
}
