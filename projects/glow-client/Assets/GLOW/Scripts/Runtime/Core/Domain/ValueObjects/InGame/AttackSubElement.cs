using System.Collections.Generic;
using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record AttackSubElement(
        MasterDataId Id,
        AttackTargetType AttackTargetType,
        IReadOnlyList<CharacterColor> TargetColors,
        IReadOnlyList<CharacterUnitRoleType> TargetRoles,
        AttackDamageType AttackDamageType,
        AttackHitData AttackHitData,
        Percentage Probability,
        AttackPowerParameter PowerParameter,
        StateEffect StateEffect)
    {
        public static AttackSubElement Empty { get; } = new(
            MasterDataId.Empty,
            AttackTargetType.All,
            new List<CharacterColor>(),
            new List<CharacterUnitRoleType>(),
            AttackDamageType.None,
            AttackHitData.Empty,
            Percentage.Empty,
            AttackPowerParameter.Empty,
            StateEffect.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
