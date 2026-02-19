using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record UnitRankCoefficient(ObscuredInt Value)
    {
        public static UnitRankCoefficient Empty { get; } = new UnitRankCoefficient(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
