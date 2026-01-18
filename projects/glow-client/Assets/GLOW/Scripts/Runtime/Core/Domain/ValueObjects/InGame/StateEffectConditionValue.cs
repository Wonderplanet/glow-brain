using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Translators;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record StateEffectConditionValue(ObscuredString Value)
    {
        public static StateEffectConditionValue Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public IReadOnlyList<CharacterUnitRoleType> ToCharacterUnitRoleTypes()
        {
            return EnumListTranslator.ToEnumList<CharacterUnitRoleType>(Value);
        }
                
        public IReadOnlyList<CharacterColor> ToCharacterColors()
        {
            return EnumListTranslator.ToEnumList<CharacterColor>(Value);
        }
    }
}