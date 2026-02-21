using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using GLOW.Scenes.PassShopProductDetail.Presentation.ViewModel;

namespace GLOW.Scenes.PassShopBuyConfirm.Presentation.ViewModel
{
    public record PassShopBuyConfirmViewModel(
        PassIconAssetPath PassIconAssetPath,
        PassProductName PassProductName,
        RawProductPriceText RawProductPriceText,
        IReadOnlyList<PassEffectViewModel> PassEffectViewModels,
        IReadOnlyList<PassReceivableRewardViewModel> PassReceivableMaxRewardViewModels)
    {
        public static PassShopBuyConfirmViewModel Empty { get; } = new(
            PassIconAssetPath.Empty,
            PassProductName.Empty,
            RawProductPriceText.Empty,
            new List<PassEffectViewModel>(),
            new List<PassReceivableRewardViewModel>());
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}