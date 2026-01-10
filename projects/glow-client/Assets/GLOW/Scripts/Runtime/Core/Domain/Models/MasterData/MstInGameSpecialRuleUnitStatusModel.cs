using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstInGameSpecialRuleUnitStatusModel(
        MasterDataId Id,
        MasterDataId GroupId,
        InGameSpecialRuleUnitStatusTargetType TargetType,
        SpecialRuleUnitStatusTargetValue TargetValue,
        InGameSpecialRuleUnitStatusParameterType StatusParameterType,
        SpecialRuleUnitStatusEffectValue EffectValue
        )
    {
        public static MstInGameSpecialRuleUnitStatusModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            InGameSpecialRuleUnitStatusTargetType.Unit,
            new SpecialRuleUnitStatusTargetValue(string.Empty),
            InGameSpecialRuleUnitStatusParameterType.Hp,
            new SpecialRuleUnitStatusEffectValue(0));

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
