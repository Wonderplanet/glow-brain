using System;
using GLOW.Core.Domain.Constants;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record ItemEffectValue(ObscuredString Value)
    {
        public static ItemEffectValue Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public CharacterColor ToCharacterColor()
        {
            if (!Enum.TryParse(Value, out CharacterColor result))
            {
                result = CharacterColor.None;
            }
            return result;
        }

        public MasterDataId ToMasterDataId()
        {
            return new MasterDataId(Value);
        }
    }
}
