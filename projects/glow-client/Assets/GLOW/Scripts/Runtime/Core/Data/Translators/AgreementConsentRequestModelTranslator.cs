using GLOW.Core.Data.Data.Agreement;
using GLOW.Core.Domain.Models.Agreement;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class AgreementConsentRequestModelTranslator
    {
        public static AgreementConsentRequestModel ToAgreementConsentRequestModel(AgreementConsentRequestData data)
        {
            return new AgreementConsentRequestModel(
                new AgreementUrl(data.Results.Url));
        }
    }
}
