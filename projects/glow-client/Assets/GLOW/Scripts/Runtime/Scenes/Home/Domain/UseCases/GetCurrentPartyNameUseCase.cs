using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class GetCurrentPartyNameUseCase
    {
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        public PartyName GetCurrentPartyName()
        {
            return PartyCacheRepository.GetCurrentPartyModel().PartyName;
        }
    }
}
