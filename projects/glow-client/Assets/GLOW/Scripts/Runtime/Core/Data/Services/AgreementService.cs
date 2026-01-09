using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.DataStores.Agreement;
using GLOW.Core.Data.Translators;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.Agreement;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using UnityHTTPLibrary;
using Zenject;

namespace GLOW.Core.Data.Services
{
    public class AgreementService : IAgreementService
    {
        [Inject] AgreementApi AgreementApi { get; }

        public async UniTask<AgreementConsentInfosModel> GetConsentInfos(
            CancellationToken cancellationToken,
            UserMyId userMyId)
        {
            var result = await AgreementApi.ConsentInfos(
                cancellationToken,
                userMyId.Value);
            return AgreementConsentInfosModelTranslator.ToAgreementConsentInfosModel(result);
        }

        public async UniTask<AgreementConsentRequestModel> GetConsentRequestUrl(
            CancellationToken cancellationToken,
            UserMyId userMyId,
            Language lang,
            AgreementCallbackUrl callbackUrl,
            AgreementBnLogoDisplayFlag bnLogo,
            IReadOnlyList<AgreementConsentType> consentTypes)
        {
            var result = await AgreementApi.ConsentRequest(
                cancellationToken,
                userMyId.Value,
                lang.ToString(),
                callbackUrl.Value,
                bnLogo.ToInt(),
                consentTypes.Select(x => x.Value).ToArray());
            return AgreementConsentRequestModelTranslator.ToAgreementConsentRequestModel(result);
        }
    }
}
