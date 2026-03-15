using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record UnitGradeCoefficient(ObscuredInt Value)
    {
        public static UnitGradeCoefficient Empty { get; } = new UnitGradeCoefficient(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
