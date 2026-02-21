using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    /// <summary>
    /// ガチャの天井に必要な回数
    /// </summary>
    public record GachaThresholdCount(ObscuredInt Value) : IGachaCountableValueObject
    {
        public ObscuredInt Value { get; } = Value > 0 ? Value : 0;

        public static GachaThresholdCount Zero = new (0);

        /// <summary>
        /// ノーマルガチャのチケット枚数分計算する
        /// </summary>
        public bool HasThreshold()
        {
            return Value > 0;
        }

        public bool IsZero()
        {
            return Value == 0;
        }
    }
}
