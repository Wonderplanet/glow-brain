using GLOW.Core.Domain.Encoder;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record UserName(ObscuredString Value)
    {
        public static UserName Empty { get; } = new (string.Empty);
        public bool IsEmpty() => ReferenceEquals(this, Empty);

        public const int MaxLength = 10;

        public static string Culling(string value)
        {
            if (null == value) return null;

            value = UserInputEncoder.Sanitize(value);

            if (value.Length > MaxLength)
            {
                return value.Substring(0, MaxLength);
            }

            return value;
        }
    }
}
