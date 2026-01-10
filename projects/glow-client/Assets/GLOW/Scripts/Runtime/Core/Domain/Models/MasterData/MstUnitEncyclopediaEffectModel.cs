using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstUnitEncyclopediaEffectModel(
        MasterDataId Id,
        MasterDataId MstUnitEncyclopediaRewardId,
        UnitEncyclopediaEffectType EffectType,
        UnitEncyclopediaEffectValue Value
    )
    {
        public static MstUnitEncyclopediaEffectModel Empty { get; } = new (
            MasterDataId.Empty,
            MasterDataId.Empty,
            UnitEncyclopediaEffectType.Hp,
            UnitEncyclopediaEffectValue.Empty
        );
    }
}
