using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    /// <summary>
    /// フィールド上のオブジェクト（キャラなど）の数
    /// </summary>
    /// <param name="Value"></param>
    public record FieldObjectCount(ObscuredInt Value)
    {
        public static FieldObjectCount Empty { get; } = new(0);
        public static FieldObjectCount Infinity { get; } = new(int.MaxValue);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
