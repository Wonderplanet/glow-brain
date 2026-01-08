using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Scenes.Shop.Domain.Model
{
    public record ConfirmationShopProductModel(
        MasterDataId OprProductId,
        ProductName ProductName,
        CostAmount CostAmount,
        RawProductPriceText RawProductPriceText,
        IsFirstTimeFreeDisplay IsFirstTimeFreeDisplay,
        IReadOnlyList<PlayerResourceModel> ProductContents)
    {
        public static ConfirmationShopProductModel Empty { get; } = new(
            MasterDataId.Empty,
            ProductName.Empty,
            CostAmount.Empty,
            RawProductPriceText.Empty,
            IsFirstTimeFreeDisplay.False,
            new List<PlayerResourceModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
