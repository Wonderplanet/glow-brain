using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Stage
{
    public record StageRecommendedLevel(ObscuredInt Value)
    {
        public static StageRecommendedLevel Empty { get; } = new (0);
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}