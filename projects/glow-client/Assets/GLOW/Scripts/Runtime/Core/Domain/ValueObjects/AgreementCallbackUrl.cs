using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record AgreementCallbackUrl(string Value)
    {
        public static AgreementCallbackUrl Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
