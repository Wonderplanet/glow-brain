using GLOW.Core.Domain.ValueObjects.InGame;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.OutpostEnhance
{
    public record OutpostEnhanceValue(ObscuredDecimal Value)
    {
        public static OutpostEnhanceValue Empty { get; } = new(0);

        public BattlePoint ToBattlePoint()
        {
            return new BattlePoint(Value);
        }

        public TickCount ToTickCount()
        {
            return new TickCount((long)Value);
        }

        public HP ToHP()
        {
            return new HP((int)Value);
        }
    }
}
