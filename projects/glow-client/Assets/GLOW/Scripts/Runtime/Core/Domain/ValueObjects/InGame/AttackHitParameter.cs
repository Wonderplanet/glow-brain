using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record AttackHitParameter(ObscuredInt Value)
    {
        public static AttackHitParameter Empty { get; } = new(0);

        public bool IsEmpty() => ReferenceEquals(this, Empty);

        public Percentage ToPercentage()
        {
            return new Percentage(Value);
        }

        public TickCount ToTickCount()
        {
            return new TickCount((int)Value);
        }
    }
}
