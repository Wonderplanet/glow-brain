using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.Models
{
    public record MstAbilityDescriptionModel(
        MasterDataId MstAbilityId,
        UnitAbilityType UnitAbilityType,
        ObscuredString Description,
        AbilityFilterTitle FilterTitle)
    {
        public static MstAbilityDescriptionModel Empty { get; } = new MstAbilityDescriptionModel(
            MasterDataId.Empty,
            UnitAbilityType.None,
            String.Empty,
            AbilityFilterTitle.Empty);
    }
}
