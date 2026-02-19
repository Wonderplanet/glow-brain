using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record KnockBackCount(ObscuredInt Value)
    {
        public static KnockBackCount Empty { get; } = new (0);

        public ObscuredInt Value { get; } = Value > 0 ? Value : 0;

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsZero()
        {
            return Value == 0;
        }
    }
}
