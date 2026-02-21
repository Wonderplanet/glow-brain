using System;
using GLOW.Core.Domain.Constants;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record SpecialRuleUnitStatusTargetValue(ObscuredString Value)
    {
        public static SpecialRuleUnitStatusTargetValue Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public CharacterColor ToCharacterColor()
        {
            return Enum.TryParse<CharacterColor>(Value, out var result) ? result : CharacterColor.None;
        }

        public CharacterUnitRoleType ToCharacterUnitRoleType()
        {
            return Enum.TryParse<CharacterUnitRoleType>(Value, out var result) ? result : CharacterUnitRoleType.None;
        }

        public MasterDataId ToMasterDataId()
        {
            return new MasterDataId(Value);
        }
    }
}
