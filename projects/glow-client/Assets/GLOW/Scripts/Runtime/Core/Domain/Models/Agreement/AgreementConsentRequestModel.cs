using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Agreement
{
    public record AgreementConsentRequestModel(AgreementUrl Url)
    {
        public static AgreementConsentRequestModel Empty { get; } = new(AgreementUrl.Empty);
    }
}
