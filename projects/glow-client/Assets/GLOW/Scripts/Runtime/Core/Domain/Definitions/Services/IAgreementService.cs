using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.Agreement;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Services
{
    public interface IAgreementService
    {
        UniTask<AgreementConsentInfosModel> GetConsentInfos(
            CancellationToken cancellationToken,
            UserMyId userMyId);

        UniTask<AgreementConsentRequestModel> GetConsentRequestUrl(
            CancellationToken cancellationToken,
            UserMyId userMyId,
            Language lang,
            AgreementCallbackUrl callbackUrl,
            AgreementBnLogoDisplayFlag bnLogo,
            IReadOnlyList<AgreementConsentType> consentTypes);
    }
}
