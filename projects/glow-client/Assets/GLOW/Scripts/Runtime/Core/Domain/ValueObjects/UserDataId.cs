using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record UserDataId(ObscuredString Value)
    {
        public static UserDataId Empty { get; } = new(string.Empty);

        public static UserDataId CreateOrEmpty(string value)
        {
            return string.IsNullOrEmpty(value) ? Empty : new UserDataId(value);
        }

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
