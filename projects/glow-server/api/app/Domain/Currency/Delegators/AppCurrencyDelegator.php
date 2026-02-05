<?php

declare(strict_types=1);

namespace App\Domain\Currency\Delegators;

use App\Domain\Common\Utils\PlatformUtil;
use App\Domain\Currency\Services\AppCurrencyService;
use App\Domain\Currency\Services\CurrencyUserService;
use App\Domain\Resource\Mst\Entities\MstStoreProductEntity;
use App\Domain\Resource\Mst\Entities\OprProductEntity;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Entities\UsrCurrencySummaryEntity;

/**
 * プロダクト側の課金周りの処理を行う
 *
 * 別ドメインに公開する内容はこのDelegatorを通す
 */
class AppCurrencyDelegator
{
    public function __construct(
        private readonly AppCurrencyService $currencyService,
        private readonly CurrencyUserService $currencyUserService,
    ) {
    }

    /**
     * mst_store_productを取得する
     *
     * @param string $id
     * @return MstStoreProductEntity|null
     */
    public function getMstStoreProductById(string $id): ?MstStoreProductEntity
    {
        return $this->currencyService->getMstStoreProductById($id);
    }

    /**
     * 課金プラットフォームとストアのプロダクトIDからmst_store_productを取得する
     *
     * @param string $productId
     * @param string $billingPlatform
     * @return MstStoreProductEntity|null
     */
    public function getMstStoreProductByProductId(string $productId, string $billingPlatform): ?MstStoreProductEntity
    {
        return $this->currencyService->getMstStoreProductByProductId($productId, $billingPlatform);
    }

    /**
     * OprProductを取得する
     *
     * @param string $id
     * @return OprProductEntity|null
     */
    public function getOprProductById(string $id): ?OprProductEntity
    {
        return $this->currencyService->getOprProductById($id);
    }

    /**
     * mst_store_product_idからOprProductを取得する
     *
     * @param string $mstProductId
     * @return OprProductEntity|null
     */
    public function getOprProductByMstProductId(string $mstProductId): ?OprProductEntity
    {
        return $this->currencyService->getOprProductByMstProductId($mstProductId);
    }

    /**
     * mst_store_product_idの配列からOprProductを一括取得
     *
     * @param array<string> $mstProductIds
     * @return Collection<string, OprProductEntity>
     */
    public function getOprProductsByMstProductIds(array $mstProductIds): Collection
    {
        return $this->currencyService->getOprProductsByMstProductIds($mstProductIds);
    }

    /**
     * billing/currencyライブラリで使用する購入プラットフォーム名を取得する
     *
     * @param string $platform
     * @return string
     */
    public function getBillingPlatform(string $platform): string
    {
        return $this->currencyService->getBillingPlatform($platform);
    }

    /**
     * プラットフォーム名から課金基盤向けのOSプラットフォームを取得
     *
     *  基盤ライブラリ側で定義されている値に変換する
     *
     * @param string $platform
     * @return string
     */
    public function getOsPlatform(string $platform): string
    {
        return $this->currencyService->getOsPlatform($platform);
    }

    /**
     * プラットフォーム別product_idと購入日時をもとにOprProductを取得する
     *
     * @param string $productId mst_store_products.product_id_ios/product_id_android
     * @param string $billingPlatform
     * @param CarbonImmutable $purchaseDate
     * @return OprProductEntity|null
     */
    public function getOprProductByProductId(
        string $productId,
        string $billingPlatform,
        CarbonImmutable $purchaseDate
    ): ?OprProductEntity {
        $mstStoreProduct = $this->currencyService->getMstStoreProductByProductId($productId, $billingPlatform);
        return $this->currencyService->getOprProductByMstProductIdAndTargetAt($mstStoreProduct->getId(), $purchaseDate);
    }

    /**
     * integerのプラットフォームから課金基盤向けのOSプラットフォームを取得
     *
     *  基盤ライブラリ側で定義されている値に変換する
     *
     * @param int $platform
     * @return string
     */
    public function getOsPlatformByIntegerPlatform(int $platform): string
    {
        return PlatformUtil::convertPlatformToCurrencyOsPlatform($platform);
    }

    /**
     * ユーザーのダイヤ情報(一次通貨情報)を取得する
     *
     * @param string $userId
     * @return UsrCurrencySummaryEntity
     */
    public function getCurrencySummary(string $userId): UsrCurrencySummaryEntity
    {
        return $this->currencyUserService->getCurrencySummary($userId);
    }

    /**
     * ユーザーの所持する無償ダイヤを取得する
     *
     * @param string $userId
     * @return integer
     */
    public function getFreeDiamond(string $userId): int
    {
        return $this->currencyUserService->getFreeDiamond($userId);
    }

    /**
     * ゲーム内で獲得した無償ダイヤを登録する
     *
     * ミッション、プレゼントなどでの獲得時に使用する
     * 課金時および広告リワードなどのダイヤでは使用しない
     *
     * @param string $userId
     * @param integer $platform
     * @param integer $amount
     * @param Trigger $trigger
     * @return UsrCurrencySummaryEntity
     */
    public function addIngameFreeDiamond(
        string $userId,
        int $platform,
        int $amount,
        Trigger $trigger,
    ): UsrCurrencySummaryEntity {
        return $this->currencyUserService->addIngameFreeDiamond(
            $userId,
            $platform,
            $amount,
            $trigger,
        );
    }

    /**
     * 必要な量のダイヤを持っているかをチェックする
     * @throws \App\Domain\Common\Exceptions\GameException
     *
     * @param string $usrUserId
     * @param integer $diamond
     * @param integer $platform
     * @param string $billingPlatform
     * @return void
     */
    public function validateDiamond(string $usrUserId, int $diamond, int $platform, string $billingPlatform): void
    {
        $this->currencyUserService->validateDiamond($usrUserId, $diamond, $platform, $billingPlatform);
    }

    /**
     * 必要な量のダイヤを持っているかをチェックする
     *
     * @param string $usrUserId
     * @param integer $diamond
     * @param integer $platform
     * @param string $billingPlatform
     * @return bool
     */
    public function isEnoughDiamondAmount(string $usrUserId, int $diamond, int $platform, string $billingPlatform): bool
    {
        return $this->currencyUserService->isEnoughDiamondAmount($usrUserId, $diamond, $platform, $billingPlatform);
    }

    /**
     * ダイヤを消費する
     *
     * 無償および有償のダイヤを消費する。消費順は無償->有償になる
     *
     * @param string $usrUserId
     * @param integer $diamond
     * @param integer $platform
     * @param string $billingPlatform
     * @param Trigger $trigger 消費理由
     * @return UsrCurrencySummaryEntity
     */
    public function consumeDiamond(
        string $usrUserId,
        int $diamond,
        int $platform,
        string $billingPlatform,
        Trigger $trigger
    ): UsrCurrencySummaryEntity {
        return $this->currencyUserService->consumeDiamond($usrUserId, $diamond, $platform, $billingPlatform, $trigger);
    }

    /**
     * 必要な量の有償ダイヤを持っているかをチェックする
     * @throws \App\Domain\Common\Exceptions\GameException
     *
     * @param string $usrUserId
     * @param integer $paidDiamond
     * @return void
     */
    public function validatePaidDiamond(string $usrUserId, int $paidDiamond): void
    {
        $this->currencyUserService->validatePaidDiamond($usrUserId, $paidDiamond);
    }

    /**
     * 必要な量の有償ダイヤを持っているかをチェックする
     *
     * @param string $usrUserId
     * @param integer $paidDiamond
     * @return bool
     */
    public function isEnoughPaidDiamond(string $usrUserId, int $paidDiamond): bool
    {
        return $this->currencyUserService->isEnoughPaidDiamond($usrUserId, $paidDiamond);
    }

    /**
     * 有償ダイヤを消費する
     *
     * @param string $usrUserId
     * @param integer $paidDiamond
     * @param integer $platform
     * @param string $billingPlatform
     * @param Trigger $trigger 消費理由
     * @return UsrCurrencySummaryEntity
     */
    public function consumePaidDiamond(
        string $usrUserId,
        int $paidDiamond,
        int $platform,
        string $billingPlatform,
        Trigger $trigger
    ): UsrCurrencySummaryEntity {
        return $this->currencyUserService->consumePaidDiamond(
            $usrUserId,
            $paidDiamond,
            $platform,
            $billingPlatform,
            $trigger
        );
    }

    public function validateAddCurrency(string $userId, int $addPaidAmount, int $addFreeAmount): void
    {
        $this->currencyUserService->validateAddCurrency($userId, $addPaidAmount, $addFreeAmount);
    }

    public function validateFreeDiamondMaxOwnedCurrency(string $userId, int $addFreeAmount): void
    {
        $this->validateAddCurrency($userId, 0, $addFreeAmount);
    }
}
