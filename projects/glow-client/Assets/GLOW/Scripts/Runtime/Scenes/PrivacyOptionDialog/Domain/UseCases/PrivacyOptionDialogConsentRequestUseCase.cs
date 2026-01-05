using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Constants;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.PrivacyOptionDialog.Domain.UseCases
{
    public class PrivacyOptionDialogConsentRequestUseCase
    {
        [Inject] IAgreementService AgreementService { get; }
        [Inject] IGameRepository GameRepository { get; }

        public async UniTask<AgreementUrl> GetConsentRequestUrl(CancellationToken cancellationToken)
        {
            var userMyId = GameRepository.GetGameFetchOther().UserProfileModel.MyId;

            var result = await AgreementService.GetConsentRequestUrl(
                cancellationToken,
                userMyId,
                Language.ja,
                new AgreementCallbackUrl(Credentials.AgreementRedirectURL),
                AgreementBnLogoDisplayFlag.True,
                new AgreementConsentType[]
                {
                    AgreementConsentType.Type0,
                    AgreementConsentType.Type1,
                    AgreementConsentType.Type2,
                    AgreementConsentType.Type3,
                });
            return result.Url;
        }
    }
}
