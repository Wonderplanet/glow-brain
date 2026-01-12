using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Title.Domains.Definition.Service;
using WPFramework.Domain.Services;
using Zenject;

namespace GLOW.Scenes.Title.Domains.UseCase
{
    public class IsUserDataCreatedUseCase
    {
        [Inject] IOverrideAuthenticateTokenService OverrideAuthenticateTokenService { get; }

        public UserDataCreatedFlag IsUserDataCreated()
        {
            var createdAccountFlag = new UserDataCreatedFlag(OverrideAuthenticateTokenService.ExistsToken());

            return createdAccountFlag;
        }
    }
}
