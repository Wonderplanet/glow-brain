<?php

declare(strict_types=1);

namespace App\Domain\Auth\UseCases;

use App\Domain\Auth\Repositories\UsrDeviceRepository;
use App\Domain\Auth\Services\IdTokenService;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Currency\Delegators\AppCurrencyDelegator;
use App\Domain\Debug\Services\DebugForDevelopService;
use App\Domain\Emblem\Delegators\EmblemDelegator;
use App\Domain\IdleIncentive\Delegators\IdleIncentiveDelegator;
use App\Domain\Mission\Delegators\MissionDelegator;
use App\Domain\Outpost\Delegators\OutpostDelegator;
use App\Domain\Resource\Log\Services\LogBankService;
use App\Domain\Resource\Mst\Repositories\MstUserLevelRepository;
use App\Domain\User\Delegators\UserDelegator;
use App\Domain\User\Repositories\UsrUserLoginRepository;
use App\Domain\User\Repositories\UsrUserParameterRepository;
use App\Domain\User\Repositories\UsrUserRepository;
use App\Infrastructure\UsrModelManager;
use WonderPlanet\Domain\Currency\Delegators\CurrencyDelegator;

class SignUpUseCase
{
    use UseCaseTrait;

    public function __construct(
        private UsrModelManager $usrModelManager,
        private Clock $clock,
        // Repository
        private MstUserLevelRepository $mstUserLevelRepository,
        // Service
        private UsrUserRepository $usrUserRepository,
        private UsrUserLoginRepository $usrUserLoginRepository,
        private UsrDeviceRepository $usrDeviceRepository,
        private IdTokenService $idTokenService,
        private UsrUserParameterRepository $usrUserParameterRepository,
        private LogBankService $logBankService,
        // Delegator
        private CurrencyDelegator $currencyDelegator,
        private AppCurrencyDelegator $appCurrencyDelegator,
        private IdleIncentiveDelegator $idleIncentiveDelegator,
        private OutpostDelegator $outpostDelegator,
        private EmblemDelegator $emblemDelegator,
        private MissionDelegator $missionDelegator,
        private UserDelegator $userDelegator,
    ) {
    }

    /**
     * @param string $platform
     * @param string $billingPlatform
     * @param string $clientUuid
     * @return array{
     *  id_token: string,
     *  currency_summary: \WonderPlanet\Domain\Currency\Entities\UsrCurrencySummaryEntity|null
     * }
     */
    public function exec(string $platform, string $billingPlatform, ?string $clientUuid): array
    {
        $now = $this->clock->now();

        try {
            $recentlyUser = null;
            if ($clientUuid !== null) {
                $recentlyUser = $this->usrUserRepository->findRecentlyCreatedAtByClientUuid($clientUuid);
            }
            $user = $this->usrUserRepository->make($now, $clientUuid);
            $this->usrModelManager->setUsrUserId($user->getId());
            /**
             * syncModelで指定したモデルのユーザーIDが
             * UsrModelManagerにセットされたユーザーIDと同じかチェックを行っているため
             * usrModelManagerにユーザーIDをセットしてからsyncModelを実行する
             */
            $this->usrUserRepository->syncModel($user);

            $mstUserLevel = $this->mstUserLevelRepository->getByLevel(1, true);
            $this->usrUserParameterRepository->create($user->getId(), $mstUserLevel->getStamina(), $now);

            // 未ログイン状態の初期値でレコード作成
            $this->usrUserLoginRepository->create($user->getId(), $now);

            $this->userDelegator->createUsrUserProfile($user->getId());

            // 初期ゲートを付与
            $this->outpostDelegator->registerInitialOutpost($user->getId());

            // 初期エンブレムを付与
            $this->emblemDelegator->registerInitialEmblems($user->getId());

            $this->idleIncentiveDelegator->createUsrIdleIncentive($user->getId(), $now);

            // ミッション機能をアンロック
            $this->missionDelegator->unlockMission($user->getId(), $now);

            // OSプラットフォーム情報登録
            $this->userDelegator->createUsrOsPlatform($user->getId(), $platform);

            // TODO: デバッグ用処理。リリース前に必ず削除する。デバッグ機能のつもりが仕様になったものもあるので、要確認。
            $debugForDevelopService = app()->make(DebugForDevelopService::class);
            /** @var DebugForDevelopService $debugForDevelopService */
            $debugForDevelopService->registerArtworks($user->getId());
            $debugForDevelopService->setDefaultOutpostArtwork($user->getId());
        } catch (\Exception $e) {
            throw new GameException(ErrorCode::USER_CREATE_FAILED, $e->getMessage());
        }

        // トランザクション処理
        list(
            $usrCurrencySummary,
            $idToken,
        ) = $this->applyUserTransactionChanges(
            function () use (
                $user,
                $now,
                $platform,
                $billingPlatform,
                $recentlyUser,
            ) {
                // ユーザーキャッシュ未適用のテーブルの更新
                $userDevice = $this->usrDeviceRepository->create($user->getId(), osPlatform: $platform);
                $idToken = $this->idTokenService->create($userDevice->getUuid());

                // 課金・通貨基盤初期化
                $osPlatform = $this->appCurrencyDelegator->getOsPlatform($platform);
                $usrCurrencySummary = $this->currencyDelegator->createUser(
                    userId: $user->getId(),
                    osPlatform: $osPlatform,
                    billingPlatform: $billingPlatform,
                    freeAmount: 0,
                );

                // APIリクエストログ保存
                $this->logBankService->createLogBankRegistered(
                    $user->getUsrUserId(),
                    $now,
                    $recentlyUser,
                );

                return [
                    $usrCurrencySummary,
                    $idToken,
                ];
            },
        );

        // レスポンス用意
        return ['id_token' => $idToken, 'currency_summary' => $usrCurrencySummary];
    }
}
