using GLOW.Core.Domain.Encoder;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record PartyName(ObscuredString Value)
    {
        public static PartyName Empty { get; } = new PartyName(string.Empty);
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
