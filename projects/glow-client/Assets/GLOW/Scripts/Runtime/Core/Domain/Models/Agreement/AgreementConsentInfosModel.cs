using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Agreement
{
    public record AgreementConsentInfosModel(Dictionary<AgreementConsentType, AgreementConsentFlag> ConsentInfos)
    {
        public static AgreementConsentInfosModel Empty { get; } = new(new Dictionary<AgreementConsentType, AgreementConsentFlag>());
    }
}
