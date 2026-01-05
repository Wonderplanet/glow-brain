using System.Collections.Generic;
using GLOW.Core.Data.Data.Agreement;
using GLOW.Core.Domain.Models.Agreement;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class AgreementConsentInfosModelTranslator
    {
        public static AgreementConsentInfosModel ToAgreementConsentInfosModel(AgreementConsentInfosData data)
        {
            var dictionary = new Dictionary<AgreementConsentType, AgreementConsentFlag>();
            foreach (var detail in data.Results.Details)
            {
                dictionary.Add(new AgreementConsentType(detail.ConsentType), AgreementConsentFlag.IntToFlag(detail.ConsentFlg));
            }

            return new AgreementConsentInfosModel(dictionary);
        }
    }
}
