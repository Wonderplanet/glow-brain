using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Scenes.PassShop.Presentation.ViewModel;

namespace GLOW.Scenes.PassShopProductDetail.Presentation.ViewModel
{
    public record PassShopProductDetailViewModel(
        PassIconAssetPath PassIconAssetPath,
        PassProductName PassProductName,
        PassDurationDay PassDurationDay,
        IReadOnlyList<PassEffectViewModel> PassEffectViewModels,
        IReadOnlyList<PassReceivableRewardViewModel> PassReceivableMaxRewardViewModels,
        PassStartAt PassStartAt,
        PassEndAt PassEndAt,
        DisplayExpirationFlag IsDisplayExpiration)
    {
        public static PassShopProductDetailViewModel Empty { get; } = new(
            PassIconAssetPath.Empty,
            PassProductName.Empty,
            PassDurationDay.Empty,
            new List<PassEffectViewModel>(),
            new List<PassReceivableRewardViewModel>(),
            PassStartAt.Empty,
            PassEndAt.Empty,
            DisplayExpirationFlag.False
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}