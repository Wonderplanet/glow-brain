using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record AutoPlayerSequenceCoefficient(ObscuredFloat Value)
    {
        public static AutoPlayerSequenceCoefficient Empty { get; } = new(0);
        public static AutoPlayerSequenceCoefficient One { get; } = new(1f);
    }
}
