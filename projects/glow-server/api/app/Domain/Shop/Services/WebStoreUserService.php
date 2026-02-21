<?php

declare(strict_types=1);

namespace App\Domain\Shop\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Utils\PlatformUtil;
use App\Domain\Shop\Constants\WebStoreConstant;
use App\Domain\Shop\Repositories\UsrWebstoreInfoRepository;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\ResultData\ShopWebstoreUserValidationResultData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\App;

/**
 * WebStoreユーザー情報管理サービス
 *
 * WebStoreユーザーに関する以下の責務を担当：
 * - ユーザー存在確認と情報取得（W1: user_validation）
 * - 国コード・OSプラットフォーム・広告ID管理
 * - 年齢制限チェック（W2: payment_validation）
 */
class WebStoreUserService
{
    public function __construct(
        private readonly UserDelegator $userDelegator,
        private readonly UsrWebstoreInfoRepository $usrWebstoreInfoRepository,
        private readonly AppShopService $appShopService,
        private readonly Clock $clock,
    ) {
    }

    /**
     * ユーザー検証を実行し、結果Entityを返す（W1: user_validation）
     *
     * @param string $bnUserId バンダイナムコID
     * @return ShopWebstoreUserValidationResultData
     * @throws GameException
     */
    public function validateUser(string $bnUserId): ShopWebstoreUserValidationResultData
    {
        // 1. bn_user_idからusrUserを検索
        $usrUser = $this->userDelegator->findByBnUserId($bnUserId);
        if (is_null($usrUser)) {
            throw new GameException(ErrorCode::WEBSTORE_USER_NOT_FOUND);
        }

        $usrUserId = $usrUser->getUsrUserId();

        // 2. BANチェック
        $now = $this->clock->now();
        $this->userDelegator->checkUserBan($usrUserId, $now);

        // 3. 誕生日情報を確認
        $usrUserProfile = $this->userDelegator->getUsrUserProfileByUsrUserId($usrUserId);

        // hasBirthDate()がtrueの場合、getBirthDate()は必ず非nullを返す
        $birthDate = $usrUserProfile->getBirthDate();
        if ($usrUserProfile->hasBirthDate()) {
            // YYYYMMDD形式
            $birthday = (string) $birthDate;
            // YYYYMM形式
            $birthdayMonth = substr((string) $birthDate, 0, 6);
        } else {
            // API上ではエラーとせず空文字で返しウェブストア側でエラーとさせる
            $birthday = '';
            $birthdayMonth = '';
        }

        // 4. レベル情報を取得
        $usrUserParameter = $this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId);

        // 5. 国コード情報を確認
        $usrWebstoreInfo = $this->usrWebstoreInfoRepository->get($usrUserId);
        if (is_null($usrWebstoreInfo)) {
            throw new GameException(ErrorCode::WEBSTORE_COUNTRY_NOT_REGISTERED);
        }

        // 6. UserValidationResultEntityを生成
        return new ShopWebstoreUserValidationResultData(
            id: $usrUserProfile->getMyId(), // ユーザに表示する検索ID
            internalId: $usrUserId,
            name: $usrUserProfile->getName(),
            level: $usrUserParameter->getLevel(),
            birthday: $birthday,
            birthdayMonth: $birthdayMonth,
            country: $usrWebstoreInfo->getCountryCode(),
        );
    }

    /**
     * WebStore情報を登録または更新
     *
     * - countryCodeが指定されていない場合は何もしない
     * - 既存レコードがある場合: 既存の値がnullの場合のみosPlatformとadIdを更新（countryCodeは更新しない）
     * - 既存レコードがない場合: 全ての情報で新規レコードを作成
     *
     * @param string $usrUserId
     * @param string|null $countryCode
     * @param int $platform プラットフォーム（1=iOS, 2=Android, 3=WebStore）
     * @param string|null $adId
     * @return void
     */
    public function registerWebStoreInfo(
        string $usrUserId,
        ?string $countryCode,
        int $platform,
        ?string $adId
    ): void {
        // countryCodeが指定されていない場合は何もしない
        if ($countryCode === null) {
            return;
        }

        // platformからos_platform文字列を取得
        $osPlatform = PlatformUtil::convertPlatformToCurrencyPlatform($platform);

        $usrWebstoreInfo = $this->usrWebstoreInfoRepository->get($usrUserId);

        // 新規レコード作成（既存レコードがない場合）
        if (is_null($usrWebstoreInfo)) {
            $this->usrWebstoreInfoRepository->create($usrUserId, $countryCode, $osPlatform, $adId);
            return;
        }

        // 既存レコードがある場合: 既存の値がnullの場合のみ更新
        $needsUpdate = false;

        if ($osPlatform && is_null($usrWebstoreInfo->getOsPlatform())) {
            $usrWebstoreInfo->setOsPlatform($osPlatform);
            $needsUpdate = true;
        }
        if ($adId && is_null($usrWebstoreInfo->getAdId())) {
            $usrWebstoreInfo->setAdId($adId);
            $needsUpdate = true;
        }

        // 更新が必要な場合のみsyncModelを実行
        if ($needsUpdate) {
            $this->usrWebstoreInfoRepository->syncModel($usrWebstoreInfo);
        }
    }

    /**
     * 購入制限をチェック（W2: payment_validation）
     *
     * @param int             $birthDate 誕生日（YYYYMMDD形式の整数）
     * @param bool            $isPaidOrder 有料商品かどうか
     * @param CarbonImmutable $now
     * @return void
     * @throws GameException 未成年で有料商品を購入しようとした場合
     */
    public function checkPurchaseRestriction(int $birthDate, bool $isPaidOrder, CarbonImmutable $now): void
    {
        // 無料商品の場合は年齢制限なし
        if (!$isPaidOrder) {
            return;
        }

        $age = $this->appShopService->calcAge($birthDate, $now);

        // 18歳未満は有料商品購入不可
        if ($age < WebStoreConstant::PURCHASE_ALLOWED_AGE) {
            throw new GameException(ErrorCode::WEBSTORE_AGE_RESTRICTION);
        }
    }

    /**
     * 本番環境でのサンドボックス決済をチェック
     *
     * @param bool $isSandbox サンドボックスモードか
     * @return void
     * @throws GameException 本番環境でサンドボックス決済の場合
     */
    public function checkSandboxInProduction(bool $isSandbox): void
    {
        if ($isSandbox && App::isProduction()) {
            throw new GameException(ErrorCode::WEBSTORE_SANDBOX_NOT_ALLOWED_IN_PRODUCTION);
        }
    }
}
