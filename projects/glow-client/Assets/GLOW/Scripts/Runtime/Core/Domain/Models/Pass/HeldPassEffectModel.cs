using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;

namespace GLOW.Core.Domain.Models.Pass
{
    public record HeldPassEffectModel(
        MasterDataId MstShopPassId,
        ShopPassEffectType ShopPassEffectType,
        PassEffectValue PassEffectValue,
        PassStartAt StartAt,
        PassEndAt EndAt)
    {
        public static HeldPassEffectModel Empty { get; } = new (
            MasterDataId.Empty,
            ShopPassEffectType.ChangeBattleSpeed,
            PassEffectValue.Empty,
            PassStartAt.Empty,
            PassEndAt.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}