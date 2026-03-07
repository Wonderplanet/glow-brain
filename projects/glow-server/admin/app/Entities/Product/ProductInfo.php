<?php

declare(strict_types=1);

namespace App\Entities\Product;

use App\Constants\BankKPIConstant;
use App\Models\Mst\MstStoreProduct;
use App\Models\Opr\OprProduct;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

abstract class ProductInfo
{
    public function __construct(
        protected OprProduct $oprProduct,
        protected MstStoreProduct $mstStoreProduct,
    ) {}

    abstract public function getName(): string;

    public function getId(): string
    {
        return $this->oprProduct->id;
    }

    public function getPaidAmount(): int
    {
        return $this->oprProduct->paid_amount;
    }

    public function getProductType(): string
    {
        return $this->oprProduct->product_type;
    }

    public function getPrice(string $platform): float
    {
        // オファーコード商品の場合は0円を返す
        // オファーコード商品：ストア登録価格は設定されているが、
        // オファーコード適用により実際の支払額が0円になる商品
        if ($this->isOfferCodeProduct()) {
            return 0;
        }

        $i18n = $this->mstStoreProduct->mst_store_product_i18n;
        if (!$i18n) {
            return 0;
        }
        return match($platform) {
            CurrencyConstants::OS_PLATFORM_IOS => $i18n->price_ios,
            CurrencyConstants::OS_PLATFORM_ANDROID => $i18n->price_android,
            BankKPIConstant::PLATFORM_ASB => $i18n->price_webstore,
            default => 0,
        };
    }

    /**
     * パック商品に含まれる有償通貨部分の価格を取得
     *
     * @param string $platform プラットフォーム
     * @return float 有償通貨部分の価格（パック商品以外は0を返す）
     */
    public function getPaidDiamondPrice(string $platform): float
    {
        $i18n = $this->mstStoreProduct->mst_store_product_i18n;
        if (!$i18n) {
            return 0;
        }
        return match($platform) {
            CurrencyConstants::OS_PLATFORM_IOS => $i18n->paid_diamond_price_ios,
            CurrencyConstants::OS_PLATFORM_ANDROID => $i18n->paid_diamond_price_android,
            BankKPIConstant::PLATFORM_ASB => $i18n->paid_diamond_price_webstore,
            default => 0,
        };
    }

    /**
     * パック商品のうち有償通貨以外の部分の価格を取得
     * (direct_dataフィールドに計上する金額)
     *
     * @param string $platform プラットフォーム
     * @return float 有償通貨以外の部分の価格 = price - paid_diamond_price
     */
    public function getDirectPrice(string $platform): float
    {
        $totalPrice = $this->getPrice($platform);
        $paidDiamondPrice = $this->getPaidDiamondPrice($platform);
        return $totalPrice - $paidDiamondPrice;
    }

    /**
     * オファーコード商品かどうかを判定
     *
     * @return bool
     */
    private function isOfferCodeProduct(): bool
    {
        // オファーコード商品のプロダクトIDリスト
        // OprProduct.id = 49, MstStoreProduct.product_id_ios = BNEI0434_offerfreediamond150
        $offerCodeProductIds = [
            '49',
        ];

        return in_array($this->oprProduct->id, $offerCodeProductIds, true);
    }
}
