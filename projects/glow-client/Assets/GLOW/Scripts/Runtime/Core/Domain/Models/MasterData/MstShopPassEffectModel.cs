using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;

namespace GLOW.Core.Domain.Models
{
    public record MstShopPassEffectModel(
        MasterDataId Id,
        MasterDataId MstShopPassId,
        ShopPassEffectType ShopPassEffectType,
        PassEffectValue EffectValue)
    {
        public static MstShopPassEffectModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            ShopPassEffectType.IdleIncentiveAddReward,
            PassEffectValue.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}