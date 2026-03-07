using GLOW.Core.Domain.ValueObjects.InGame;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.ArtworkEffect
{
    public record ArtworkEffectValue(ObscuredDecimal Value)
    {
        public static ArtworkEffectValue Empty { get; } = new (0);
        public static ArtworkEffectValue Zero { get; } = new (0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public static ArtworkEffectValue operator +(ArtworkEffectValue a, ArtworkEffectValue b)
        {
            return new(a.Value + b.Value);
        }

        public BattlePoint ToBattlePoint()
        {
            return new BattlePoint(Value);
        }

        public TickCount ToTickCount()
        {
            return new TickCount((long)Value);
        }

        public PercentageM ToPercentageM()
        {
            return new PercentageM(Value);
        }
    }
}
