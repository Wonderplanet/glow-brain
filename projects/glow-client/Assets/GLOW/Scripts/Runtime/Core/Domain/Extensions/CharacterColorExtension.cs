using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.Extensions
{
    public static class CharacterColorExtension
    {
        /// <summary> 自身の色属性と対象の色属性を比較し、自身側が有利な属性か返す </summary>
        public static bool IsAdvantage(this CharacterColor selfColor, CharacterColor targetColor)
        {
            return selfColor switch
            {
                CharacterColor.Red => targetColor == CharacterColor.Green,
                CharacterColor.Green => targetColor == CharacterColor.Yellow,
                CharacterColor.Yellow => targetColor == CharacterColor.Blue,
                CharacterColor.Blue => targetColor == CharacterColor.Red,
                _ => false
            };
        }
    }
}
