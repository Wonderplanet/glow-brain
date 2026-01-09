using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Scenes.PassShop.Domain.Model;
using GLOW.Scenes.PassShopProductDetail.Domain.Model;

namespace GLOW.Scenes.PassShopBuyConfirm.Domain.Model
{
    public record PassShopBuyConfirmModel(
        PassIconAssetPath PassIconAssetPath,
        PassProductName PassProductName,
        RawProductPriceText RawProductPriceText,
        IReadOnlyList<PassEffectModel> PassEffectModels,
        IReadOnlyList<PassReceivableRewardModel> PassReceivableMaxRewardModels)
    {
        public static PassShopBuyConfirmModel Empty { get; } = new(
            PassIconAssetPath.Empty,
            PassProductName.Empty,
            RawProductPriceText.Empty,
            new List<PassEffectModel>(),
            new List<PassReceivableRewardModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}