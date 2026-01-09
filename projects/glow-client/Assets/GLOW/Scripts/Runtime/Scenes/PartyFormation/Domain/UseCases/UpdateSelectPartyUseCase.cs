using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.PartyFormation.Domain.UseCases
{
    public class UpdateSelectPartyUseCase
    {
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }

        public void UpdateSelectParty(PartyNo partyNo)
        {
            PartyCacheRepository.SetSelectPartyNo(partyNo);
        }
    }
}
