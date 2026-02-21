using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.BoxGacha
{
    public record BoxDrawCount(ObscuredInt Value)
    {
        public static BoxDrawCount Empty { get; } = new(0);
        
        public static bool operator ==(BoxDrawCount a, BoxGachaPrizeStock b)
        {
            return a.Value == b.Value;
        }
        
        public static bool operator !=(BoxDrawCount a, BoxGachaPrizeStock b)
        {
            return a.Value != b.Value;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}