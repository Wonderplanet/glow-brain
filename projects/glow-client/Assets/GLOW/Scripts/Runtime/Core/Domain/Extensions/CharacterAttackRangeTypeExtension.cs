using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.Extensions
{
    public static class CharacterAttackRangeTypeExtension
    {
        public static string ToLocalizeString(this CharacterAttackRangeType type) 
        {
            return type switch 
            {
                CharacterAttackRangeType.Short => "近距離",
                CharacterAttackRangeType.Middle => "中距離",
                CharacterAttackRangeType.Long => "遠距離",
                _ => string.Empty,
            };
        }
    }
}
