using UnityEngine;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    /// <summary> インゲーム内制限時間のテキストカラー </summary>
    public record RemainingTimeTextColor(Color Color)
    {
        public static RemainingTimeTextColor Default { get; } = new RemainingTimeTextColor(Color.white);
        public static RemainingTimeTextColor Highlight { get; } = new RemainingTimeTextColor(new Color32(238,54,50, 255));

        public static RemainingTimeTextColor GetColor(bool isHighlightColor)
        {
            return isHighlightColor ? Highlight : Default;
        }
    }
}
