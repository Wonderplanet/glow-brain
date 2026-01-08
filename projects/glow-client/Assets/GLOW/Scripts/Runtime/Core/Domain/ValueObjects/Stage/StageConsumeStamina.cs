using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Stage
{
    public record StageConsumeStamina(ObscuredInt Value)
    {
        public static StageConsumeStamina Empty { get; } = new(0);

        public bool IsZero()
        {
            return Value == 0;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public override string ToString()
        {
            return Value.ToString();
        }

        public static StageConsumeStamina operator *(StageConsumeStamina a, int b)
        {
            return new(a.Value * b);
        }
    }
}
