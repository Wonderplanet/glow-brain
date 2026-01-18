using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Scenes.PassShop.Domain.Model
{
    public record PassShopProductModel(
        MasterDataId MstShopPassId,
        ShopProductId ShopProductId,
        PassProductName PassProductName,
        PassDurationDay PassDurationDay,
        ShopPassCellColor ShopPassCellColor,
        DisplayExpirationFlag IsDisplayExpiration,
        PassIconAssetPath PassIconAssetPath,
        IReadOnlyList<PassEffectModel> PassEffectModels,
        IReadOnlyList<PlayerResourceModel> PassDailyRewardModels,
        IReadOnlyList<PlayerResourceModel> PassImmediatelyRewardModels,
        RawProductPriceText RawProductPriceText,
        PassStartAt PassStartAt,
        PassEndAt PassEndAt,
        RemainingTimeSpan PassEffectValidRemainingTime)
    {
        public static PassShopProductModel Empty { get; } = new(
            MasterDataId.Empty,
            ShopProductId.Empty,
            PassProductName.Empty,
            PassDurationDay.Empty,
            ShopPassCellColor.Purple,
            DisplayExpirationFlag.False,
            PassIconAssetPath.Empty,
            new List<PassEffectModel>(),
            new List<PlayerResourceModel>(),
            new List<PlayerResourceModel>(),
            RawProductPriceText.Empty,
            PassStartAt.Empty,
            PassEndAt.Empty,
            RemainingTimeSpan.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
