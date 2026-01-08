using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Scenes.PackShop.Domain.Models
{
    public record PackShopProductModel(
        MasterDataId OprProductId,
        NewFlag NewFlag,
        ProductName ProductName,
        DisplayCostType ProductPriceType,
        ProductPrice ProductPrice,
        RawProductPriceText RawProductPriceText,
        DiscountRate DiscountRate,
        PurchasableCount PurchasableCount,
        EndDateTime EndDateTime,
        IReadOnlyList<PlayerResourceModel> Items,
        PackBannerAssetPath BannerAssetPath,
        PackDecoration? Decoration,
        IsFirstTimeFreeDisplay IsFirstTimeFreeDisplay
        );
}
