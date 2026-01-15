using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    /// <summary>
    /// ガチャの引ける上限回数
    /// </summary>
    public record GachaDrawLimitCount(ObscuredInt Value) : IGachaCountableValueObject
    {
        public static GachaDrawLimitCount Zero { get; } = new(0);

        public static GachaDrawLimitCount Unlimited { get; } = new(-1);
        
        public static AdGachaDrawableCount operator -(GachaDrawLimitCount a, GachaPlayedCount b)
        {
            var result = a.Value - b.Value;
            return new AdGachaDrawableCount(result);
        }
        
        public AdGachaDrawableCount ToAdGachaDrawableCount()
        {
            return new AdGachaDrawableCount(Value);
        }

        public bool IsUnlimited()
        {
            return ReferenceEquals(this, Unlimited);
        }
        
        public bool IsZero()
        {
            return Value == 0;
        }

        public bool HasValue()
        {
            return Value > 0;
        }
    }
}
