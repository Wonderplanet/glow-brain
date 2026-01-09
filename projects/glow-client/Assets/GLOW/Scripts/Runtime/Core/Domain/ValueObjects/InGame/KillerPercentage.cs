using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record KillerPercentage(ObscuredInt Value)
    {
        public static KillerPercentage Empty { get; } = new(0);
        public static KillerPercentage Hundred { get; } = new(100);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public PercentageM ToPercentageM()
        {
            return new PercentageM(Value);
        }
    }
}
