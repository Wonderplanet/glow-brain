using System;
using GLOW.Core.Domain.Constants;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.ArtworkEffect
{
    public record ArtworkEffectActivationValue(ObscuredString Value)
    {
        public static ArtworkEffectActivationValue Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public int ToInt()
        {
            if (Int32.TryParse(Value, out var result))
            {
                return result;
            }

            return 0;
        }

        public MasterDataId ToMasterDataId()
        {
            return new MasterDataId(Value);
        }

        public CharacterUnitRoleType ToCharacterUnitRoleType()
        {
            return Enum.Parse<CharacterUnitRoleType>(Value);
        }

        public CharacterColor ToCharacterColor()
        {
            return Enum.Parse<CharacterColor>(Value);
        }
    }
}
