using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PartyNameEdit.Domain.Models;
using Zenject;

namespace GLOW.Scenes.PartyNameEdit.Domain.UseCases
{
    public class GetPartyNameUseCase
    {
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }

        public PartyNameEditDialogModel GetPartyName(PartyNo no)
        {
            var party = PartyCacheRepository.GetCacheParty(no);
            return new PartyNameEditDialogModel(party.PartyName);
        }
    }
}
