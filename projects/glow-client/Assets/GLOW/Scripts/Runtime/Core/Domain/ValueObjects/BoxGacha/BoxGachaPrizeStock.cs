using GLOW.Core.Domain.ValueObjects.Gacha;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.BoxGacha
{
    public record BoxGachaPrizeStock(ObscuredInt Value)
    {
        public static BoxGachaPrizeStock Empty { get; } = new(0);
        
        public static bool operator ==(GachaDrawCount left, BoxGachaPrizeStock right)
        {
            if (left == null || right == null) return false;
            return left.Value == right.Value;
        }

        public static bool operator !=(GachaDrawCount left, BoxGachaPrizeStock right)
        {
            return !(left == right);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public override string ToString()
        {
            return Value.ToString();
        }
    }
}