using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record KomaEffectParameter(ObscuredString Value)
    {
        public static KomaEffectParameter Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public StateEffectParameter ToStateEffectParameter()
        {
            return new StateEffectParameter(decimal.Parse(Value));
        }

        public TickCount ToTickCount()
        {
            return new TickCount(int.Parse(Value));
        }

        public int ToInt()
        {
            return int.Parse(Value);
        }

        public float ToFloat()
        {
            return float.Parse(Value);
        }
    }
}
