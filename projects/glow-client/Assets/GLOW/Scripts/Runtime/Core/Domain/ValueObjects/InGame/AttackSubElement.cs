using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record AttackSubElement(
        MasterDataId Id,
        AttackTargetType AttackTargetType,
        IReadOnlyList<CharacterColor> TargetColors,
        IReadOnlyList<CharacterUnitRoleType> TargetRoles,
        IReadOnlyList<MasterDataId> TargetSeriesIds,
        IReadOnlyList<MasterDataId> TargetCharacterIds,
        AttackDamageType AttackDamageType,
        AttackHitData AttackHitData,
        Percentage Probability,
        AttackPowerParameter PowerParameter,
        StateEffect StateEffect)
    {
        public static AttackSubElement Empty { get; } = new(
            MasterDataId.Empty,
            AttackTargetType.All,
            Array.Empty<CharacterColor>(),
            Array.Empty<CharacterUnitRoleType>(),
            Array.Empty<MasterDataId>(),
            Array.Empty<MasterDataId>(),
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
