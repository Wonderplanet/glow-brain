using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Scenes.PassShop.Domain.Model;

namespace GLOW.Scenes.PassShopProductDetail.Domain.Model
{
    public record PassShopProductDetailModel(
        PassIconAssetPath PassIconAssetPath,
        PassProductName PassProductName,
        PassDurationDay PassDurationDay,
        IReadOnlyList<PassEffectModel> PassEffectModels,
        IReadOnlyList<PassReceivableRewardModel> PassReceivableMaxRewardModels,
        PassStartAt PassStartAt,
        PassEndAt PassEndAt,
        DisplayExpirationFlag IsDisplayExpiration)
    {
        public static PassShopProductDetailModel Empty { get; } = new(
            PassIconAssetPath.Empty,
            PassProductName.Empty,
            PassDurationDay.Empty,
            new List<PassEffectModel>(),
            new List<PassReceivableRewardModel>(),
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