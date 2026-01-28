using GLOW.Core.Domain.Models;

namespace GLOW.Core.Domain.ValueObjects.Shop
{
    public record ValidatedStoreProductModel(
        MstStoreProductModel MstStoreProduct,
        ProductPrice StorePrice,
        CurrencyCode StoreCurrencyCode,
        RawProductPriceText RawProductPriceText
    )
    {
        public static ValidatedStoreProductModel Empty { get; } = new ValidatedStoreProductModel(
            MstStoreProductModel.Empty,
            ProductPrice.Empty,
            CurrencyCode.Empty,
            RawProductPriceText.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        // 商品価格の取得
        // FakeStore中の価格をマスター設定のものにする場合ここで分岐させる
        public ProductPrice GetPrice()
        {
            return StorePrice;
        }
    }
}
