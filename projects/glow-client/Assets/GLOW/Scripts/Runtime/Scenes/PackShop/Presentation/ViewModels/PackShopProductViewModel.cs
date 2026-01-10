using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.PassShop.Presentation.ViewModel;

namespace GLOW.Scenes.PackShop.Presentation.ViewModels
{
    public record PackShopProductViewModel(
        MasterDataId OprProductId,
        NewFlag NewFlag,
        ProductName ProductName,
        DisplayCostType DisplayCostType,
        ProductPrice ProductPrice,
        RawProductPriceText RawProductPriceText,
        DiscountRate DiscountRate,
        PurchasableCount PurchasableCount,
        EndDateTime EndDateTime,
        IReadOnlyList<PlayerResourceIconViewModel> Items,
        PackBannerAssetPath BannerAssetPath,
        PackDecoration? Decoration,
        HeldAdSkipPassInfoViewModel HeldAdSkipPassInfo,
        IsFirstTimeFreeDisplay IsFirstTimeFreeDisplay
    )
    {
        public static PackShopProductViewModel Empty { get; } = new(
            MasterDataId.Empty,
            NewFlag.Empty,
            ProductName.Empty,
            DisplayCostType.Cash,
            ProductPrice.Empty,
            RawProductPriceText.Empty,
            DiscountRate.Empty,
            PurchasableCount.Empty,
            EndDateTime.Empty,
            new List<PlayerResourceIconViewModel>(),
            PackBannerAssetPath.Empty,
            null,
            HeldAdSkipPassInfoViewModel.Empty,
            IsFirstTimeFreeDisplay.False
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
