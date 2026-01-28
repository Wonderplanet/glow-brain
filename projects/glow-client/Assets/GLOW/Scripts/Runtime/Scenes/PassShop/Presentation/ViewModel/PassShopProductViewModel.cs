using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.PassShop.Presentation.ViewModel
{
    public record PassShopProductViewModel(
        MasterDataId MstShopPassId,
        ShopProductId ShopProductId,
        PassProductName PassProductName,
        PassDurationDay PassDurationDay,
        ShopPassCellColor ShopPassCellColor,
        DisplayExpirationFlag IsDisplayExpiration,
        PassIconAssetPath PassIconAssetPath,
        IReadOnlyList<PassEffectViewModel> PassEffectViewModels,
        IReadOnlyList<PlayerResourceIconViewModel> PassDailyRewardViewModels,
        IReadOnlyList<PlayerResourceIconViewModel> PassImmediatelyRewardViewModels,
        RawProductPriceText RawProductPriceText,
        PassStartAt PassStartAt,
        PassEndAt PassEndAt,
        RemainingTimeSpan RemainingTimeSpan)
    {
        public static PassShopProductViewModel Empty { get; } = new(
            MasterDataId.Empty,
            ShopProductId.Empty,
            PassProductName.Empty,
            PassDurationDay.Empty,
            ShopPassCellColor.Purple,
            DisplayExpirationFlag.False,
            PassIconAssetPath.Empty,
            new List<PassEffectViewModel>(),
            new List<PlayerResourceIconViewModel>(),
            new List<PlayerResourceIconViewModel>(),
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
