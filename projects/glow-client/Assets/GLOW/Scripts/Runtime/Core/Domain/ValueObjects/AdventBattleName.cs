using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record AdventBattleName(ObscuredString Value)
    {
        public static AdventBattleName Empty { get; } = new(string.Empty);

        public override string ToString()
        {
            return Value.ToString();
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}