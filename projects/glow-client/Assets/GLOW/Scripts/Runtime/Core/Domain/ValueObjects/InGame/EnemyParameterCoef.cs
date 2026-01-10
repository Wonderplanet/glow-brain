using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record EnemyParameterCoef(ObscuredFloat Value)
    {
        public static EnemyParameterCoef Empty { get; } = new (1f);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
