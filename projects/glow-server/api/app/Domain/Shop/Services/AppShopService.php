<?php

declare(strict_types=1);

namespace App\Domain\Shop\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Utils\StringUtil;
use App\Domain\Currency\Delegators\AppCurrencyDelegator;
use App\Domain\Resource\Mst\Entities\OprProductEntity;
use App\Domain\Shop\Entities\UsrStoreInfoEntity as ShopUsrStoreInfoEntity;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Delegators\UserDelegator;
use Carbon\CarbonImmutable;
use WonderPlanet\Domain\Billing\Delegators\BillingDelegator;
use WonderPlanet\Domain\Billing\Entities\UsrStoreInfoEntity;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

/**
 * デモアプリケーションの課金周りのServiceクラス
 *
 * ライブラリと名前が被ってしまったのでAppをつけた
 */
class AppShopService
{
    public function __construct(
        private readonly AppCurrencyDelegator $appCurrencyDelegator,
        private readonly UserDelegator $userDelegator,
        private readonly BillingDelegator $billingDelegator,
        private readonly Clock $clock,
    ) {
    }

    public const GUIDLINE_CHECK_CURRENCY_CODE = "JPY";

    // 課金基盤に関わる定数で、他の箇所から参照される想定はないため、private定数として定義しておく

    // 購入限度額

    /** @var int  この年齢以下は、限度額が最少 */
    private const MIN_PURCHASE_LIMIT_AGE = 15;

    /** @var int  この年齢以下は、限度額が2番目に少ない */
    private const MIDDLE_PURCHASE_LIMIT_AGE = 17;

    /** @var int  この年齢以上は、限度額なし */
    private const PURCHASE_LIMIT_NONE_AGE = 18;

    /**
     * 年齢が{key}以下のユーザーの場合、{value}を超える課金額は許可しない
     * @var array<mixed>
     */
    private const PURCHASE_LIMIT_MAX_MONTHLY_PAID_AMOUNT = [
        self::MIN_PURCHASE_LIMIT_AGE => 5000,
        self::MIDDLE_PURCHASE_LIMIT_AGE => 20000,
    ];

    /**
     * 有効なopr_productsデータを取得する
     * 該当データがない場合はエラーを返す
     *
     * @param bool $needCheckActive true: 期間チェックもする, false: 期間チェックはしない
     */
    public function getValidOprProductById(
        string $productSubId,
        CarbonImmutable $now,
        bool $needCheckActive = true,
    ): OprProductEntity {
        $oprProduct = $this->appCurrencyDelegator->getOprProductById($productSubId);
        if (
            is_null($oprProduct)
            || (
                $needCheckActive
                && !$now->between($oprProduct->getStartDate(), $oprProduct->getEndDate())
            )
        ) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                "opr_products record is not found. (id: $productSubId})",
            );
        }

        return $oprProduct;
    }

    /**
     * product_sub_idとbilling_platformからproduct_idを取得する
     *
     * @param string $productSubId
     * @param string $billingPlatform
     * @return string
     */
    public function getProductIdByProductSubId(string $productSubId, string $billingPlatform): string
    {
        $oprProduct = $this->appCurrencyDelegator->getOprProductById($productSubId);
        $mstStoreProduct = $this->appCurrencyDelegator->getMstStoreProductById($oprProduct->getMstStoreProductId());

        // $billingPlatformによって戻り値を変える
        switch ($billingPlatform) {
            case CurrencyConstants::PLATFORM_APPSTORE:
                return $mstStoreProduct->getProductIdIos();
            case CurrencyConstants::PLATFORM_GOOGLEPLAY:
                return $mstStoreProduct->getProductIdAndroid();
            default:
                return '';
        }
    }

    /**
     * 年齢から次の課金額リセット日時を計算する
     *
     * 課金限度額ありの年齢の場合は、月毎に課金額リセットを行うため、翌月1日0時とする
     *
     * glowでは、renotify_atをリセット日時として利用しています
     *
     * @param integer $age
     * @return string|null
     */
    public function calcStorePaidPriceNextResetAt(int $age, CarbonImmutable $now): ?string
    {
        $renotifyAt = null;

        if ($this->isPurchaseLimitNone($age) === false) {
            // 課金限度額ありの年齢の場合
            $renotifyAt = $this->clock->setDefaultTimezone(
                $this->clock->setLogicTimezone($now)
                    ->setTimeFromTimeString(Clock::REAL_BORDER_TIME_DAY_START)
                    ->startOfMonth()
                    ->addMonth(),
            )->toDateTimeString();
        }

        return $renotifyAt;
    }

    /**
     * 年齢に応じた累計課金額の上限を超えないか確認する
     */
    public function validateStorePaidPrice(string $usrUserId, string $purchasePrice): void
    {
        if (StringUtil::isNotSpecified($purchasePrice)) {
            throw new GameException(
                ErrorCode::INVALID_PARAMETER,
                "PurchasePrice is invalid. purchasePrice: {$purchasePrice}"
            );
        }

        $usrStoreInfo = $this->billingDelegator->getStoreInfo($usrUserId);
        if (is_null($usrStoreInfo)) {
            throw new GameException(
                ErrorCode::BILLING_SHOP_INFO_NOT_FOUND,
                "store info not found."
            );
        }

        $age = $usrStoreInfo->getAge();
        $paidPrice = $usrStoreInfo->getPaidPrice() + round((float) $purchasePrice);

        $purchaseLimit = $this->getPurchaseLimit($age);
        if (is_null($purchaseLimit)) {
            return;
        }

        if ($paidPrice > $purchaseLimit) {
            throw new GameException(
                ErrorCode::BILLING_UNDERAGE_PURCHASE_LIMIT_EXCEEDED,
                "Over paid price. age: {$age} paidPrice:{$paidPrice} purchasePrice: {$purchasePrice}"
            );
        }
    }

    /**
     * 年齢に応じた購入限度額を取得する
     *
     * @param int $age
     * @return int|null 購入限度額 nullの場合は無制限、0の場合は購入不可
     */
    private function getPurchaseLimit(int $age): ?int
    {
        if ($this->isPurchaseLimitNone($age)) {
            // 購入限度額が無制限の年齢
            return null;
        }

        $limits = self::PURCHASE_LIMIT_MAX_MONTHLY_PAID_AMOUNT;
        ksort($limits);

        foreach ($limits as $limitAge => $limit) {
            if ($age <= $limitAge) {
                // 低い年齢から順番に、適用すべき限度額を確認する
                return $limit;
            }
        }

        return 0;
    }

    /**
     * 生年月日数字から年齢を計算する
     *
     * @param int $birthDate 8桁の生年月日の数値データ（例: 20011205 = 2001年12月5日が誕生日）
     * @return int
     */
    public function calcAge(int $birthDate, CarbonImmutable $now): int
    {
        $birthDateString = strval($birthDate);

        $birthDate = CarbonImmutable::createFromFormat('Ymd', $birthDateString);

        return (int) $birthDate->diffInYears($now, false);
    }

    /**
     * ユーザーのショップ登録情報を、年齢未登録の状態で、新規作成する
     *
     * バリデーション入れていて発生頻度はほぼないが、念の為、未成年扱いでレコード作成する
     *  - 未成年ユーザーが制限なしで課金できる状態を避けるため
     *  - 成年ユーザーに制限がかかってしまう可能性は、ここでは許容する
     *
     * renotify_atを現在日時にしています
     *  - 課金額制限のチェックに使う、課金額加算処理を行うためには、非nullの値設定が必要
     *  - 初課金時に課金額がリセットされる処理になるが、未課金状態なので問題なし
     */
    public function createUsrStoreInfo(string $usrUserId, CarbonImmutable $now): UsrStoreInfoEntity
    {
        return $this->billingDelegator->setStoreInfo(
            $usrUserId,
            UserConstant::AGE_DEFAULT,
            $now->toDateTimeString(),
        );
    }

    /**
     * 年齢情報を更新し、課金額をリセットし、ユーザーのショップ登録情報を初期化する。
     * shop/set_store_info APIでのみ使用想定。
     *
     * @param int $birthDate 8桁の生年月日の数値データ（例: 20011205 = 2001年12月5日が誕生日）
     */
    public function initUsrStoreInfo(
        string $usrUserId,
        CarbonImmutable $now,
        int $birthDate,
    ): UsrStoreInfoEntity {
        $age = $this->calcAge($birthDate, $now);
        $renotifyAt = $this->calcStorePaidPriceNextResetAt($age, $now);

        // 累積課金額は0にリセットし更新する
        return $this->billingDelegator->setStoreInfo(
            $usrUserId,
            $age,
            $renotifyAt,
        );
    }

    /**
     * ユーザーのショップ登録情報を更新する
     *
     * 年齢情報、リセット日時、累計課金額を更新する。
     */
    public function updateUsrStoreInfo(
        string $usrUserId,
        CarbonImmutable $now,
    ): void {
        $usrStoreInfo = $this->createShopUsrStoreInfoEntity(
            $usrUserId,
            $now,
        );
        if (is_null($usrStoreInfo)) {
            // UsrStoreInfoが存在しない場合は何もしない
            return;
        }

        if ($usrStoreInfo->isResetPaidPrice()) {
            // 課金額がリセットされた場合は、課金額を0に更新する
            $this->billingDelegator->setStoreInfo(
                $usrUserId,
                $usrStoreInfo->getAge(),
                $usrStoreInfo->getRenotifyAt(),
            );
        } elseif ($usrStoreInfo->isAgeChanged() || $usrStoreInfo->isRenotifyAtChanged()) {
            // 課金額がリセットされていない場合は、年齢とリセット日時のみ更新
            $this->billingDelegator->upsertStoreInfoAge(
                $usrUserId,
                $usrStoreInfo->getAge(),
                $usrStoreInfo->getRenotifyAt(),
            );
        }
    }

    /**
     * 課金額をリセットするかどうか
     * true: リセットする, false: リセットしない
     */
    private function isResetPaidPrice(?string $renotifyAt, CarbonImmutable $now): bool
    {
        if (is_null($renotifyAt)) {
            // リセット日が未設定ユーザーは、課金額無制限とみなしています
            return false;
        }

        // 現在日時が、リセット日時を過ぎていれば、リセットする(true)
        return $now->gte($renotifyAt);
    }

    /**
     * 課金無制限の年齢かどうか
     * true: 無制限, false: 制限あり
     */
    private function isPurchaseLimitNone(int $age): bool
    {
        return $age >= self::PURCHASE_LIMIT_NONE_AGE;
    }

    /**
     * ユーザーの年齢情報とリセット日時を計算して、ShopUsrStoreInfoEntityを作成する
     */
    private function createShopUsrStoreInfoEntity(
        string $usrUserId,
        CarbonImmutable $now,
    ): ?ShopUsrStoreInfoEntity {
        $usrStoreInfo = $this->billingDelegator->getStoreInfo($usrUserId);
        if (is_null($usrStoreInfo)) {
            // UsrStoreInfoが存在しない場合は何もしない
            return null;
        }

        $beforeAge = $usrStoreInfo->getAge();
        $beforeRenotifyAt = $usrStoreInfo->getRenotifyAt();
        $beforePaidPrice = $usrStoreInfo->getPaidPrice();
        if ($this->isPurchaseLimitNone($beforeAge)) {
            // すでに課金無制限の年齢になっている場合は、更新処理は不要なので、ここで終了
            return new ShopUsrStoreInfoEntity(
                $usrUserId,
                $beforeAge,
                $beforeAge,
                $beforeRenotifyAt,
                $beforeRenotifyAt,
                $beforePaidPrice,
                $beforePaidPrice
            );
        }

        $usrUserProfile = $this->userDelegator->getUsrUserProfileByUsrUserId($usrUserId);
        $birthDate = $usrUserProfile->getBirthDate();
        if (is_null($birthDate)) {
            // 年齢登録がなければ課金はできないので、nullを返す（usr_store_infosがないのと同等）
            return null;
        }
        $age = $this->calcAge($birthDate, $now);
        $renotifyAt = $this->calcStorePaidPriceNextResetAt($age, $now);

        $paidPrice = $usrStoreInfo->getPaidPrice();
        if (
            $this->isResetPaidPrice($beforeRenotifyAt, $now)
            || $this->isPurchaseLimitNone($age)
        ) {
            $paidPrice = 0;
        }

        return new ShopUsrStoreInfoEntity(
            $usrUserId,
            $beforeAge,
            $age,
            $beforeRenotifyAt,
            $renotifyAt,
            $beforePaidPrice,
            $paidPrice
        );
    }

    /**
     * ユーザーのショップ登録情報をリセット考慮して取得する
     */
    public function fetchUsrStoreInfo(
        string $usrUserId,
        CarbonImmutable $now
    ): ?ShopUsrStoreInfoEntity {
        return $this->createShopUsrStoreInfoEntity($usrUserId, $now);
    }

    /**
     * 課金処理前に、年齢情報を更新し、課金可能な状態か確認する
     */
    public function updateAndValidateUsrStoreInfoForPurchase(
        string $usrUserId,
        CarbonImmutable $now,
        string $purchasePrice,
        string $currencyCode,
    ): void {
        $usrStoreInfo = $this->billingDelegator->getStoreInfo($usrUserId);

        if (is_null($usrStoreInfo)) {
            throw new GameException(
                ErrorCode::BILLING_SHOP_INFO_NOT_FOUND,
                "store info not found."
            );
        }

        // 課金処理前に年齢未設定の場合はエラー
        $usrUserProfile = $this->userDelegator->getUsrUserProfileByUsrUserId($usrUserId);
        if ($usrUserProfile->hasBirthDate() === false) {
            throw new GameException(
                ErrorCode::USER_BIRTHDATE_NOT_REGISTERED,
                "birth date is not set."
            );
        }

        $this->updateUsrStoreInfo($usrUserId, $now);

        // 日本円のみ課金額制限のチェック
        if ($currencyCode === self::GUIDLINE_CHECK_CURRENCY_CODE) {
            $this->validateStorePaidPrice($usrUserId, $purchasePrice);
        }
    }


    /**
     * @param string $usrUserId
     * @return string
     */
    public function getDeviceId(string $usrUserId): string
    {
        $clientUuid = $this->userDelegator->getUsrUserByUsrUserId($usrUserId)->getClientUuid();

        $deviceId = "userid_" . $usrUserId;
        if (!is_null($clientUuid)) {
            $deviceId = "clientuuid_" . $clientUuid;
        }

        return $deviceId;
    }

    /**
     * オファーコード商品かどうかを判定
     *
     * オファーコード商品：ストア登録価格は設定されているが、
     * オファーコード適用により実際の支払額が0円になる商品
     *
     * @param string $oprProductId
     * @return bool
     */
    public function isOfferCodeProduct(string $oprProductId): bool
    {
        // オファーコード商品のプロダクトIDリスト
        // OprProduct.id = 49, MstStoreProduct.product_id_ios = BNEI0434_offerfreediamond150
        $offerCodeProductIds = [
            '49',
        ];

        return in_array($oprProductId, $offerCodeProductIds, true);
    }
}
