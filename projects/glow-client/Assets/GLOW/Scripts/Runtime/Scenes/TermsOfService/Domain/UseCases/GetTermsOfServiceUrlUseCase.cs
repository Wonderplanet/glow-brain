using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.TermsOfService.Domain.Model;
using Zenject;

namespace GLOW.Scenes.TermsOfService.Domain.UseCases
{
    public class GetTermsOfServiceUrlUseCase
    {
        [Inject] IGameRepository GameRepository { get; }

        public TermsUrlModel GetTermsUrlModel()
        {
            var gameVersionModel = GameRepository.GetGameVersion();

            return new TermsUrlModel(
                gameVersionModel.TosUrl,
                gameVersionModel.PrivacyPolicyUrl,
                gameVersionModel.GlobalConsentUrl,
                gameVersionModel.InAppAdvertisementTermsUrl);
        }
    }
}
