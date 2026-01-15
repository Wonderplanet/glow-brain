using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using Zenject;

namespace GLOW.Scenes.TermsOfService.Domain.UseCases
{
    public class TermsOfServiceUseCases
    {
        [Inject] IUserService UserService { get; }
        [Inject] IGameRepository GameRepository { get; }

        public async UniTask AgreeToLicense(CancellationToken cancellationToken)
        {
            var gameVersionModel = GameRepository.GetGameVersion();
            // NOTE: 同意した場合は同意バージョンを更新する
            await UserService.Agree(
                cancellationToken,
                gameVersionModel.TosVersion,
                gameVersionModel.PrivacyPolicyVersion,
                gameVersionModel.GlobalConsentVersion,
                gameVersionModel.InAppAdvertisementTermsVersion);
        }
    }
}
