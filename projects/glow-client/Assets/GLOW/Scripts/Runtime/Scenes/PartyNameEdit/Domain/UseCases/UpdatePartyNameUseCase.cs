using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models.Party;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.PartyNameEdit.Domain.UseCases
{
    public class UpdatePartyNameUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IPartyService PartyService { get; }

        public async UniTask UpdatePartyName(CancellationToken ct, PartyNo no, string newPartyName)
        {
            var party = PartyCacheRepository.GetCacheParty(no);
            var partyName = new PartyName(newPartyName);

            var result = await PartyService.Save(ct, party.PartyNo, partyName, party.GetUnitList());
            UpdatePartySaveResult(result);

            PartyCacheRepository.UpdateParty(party.PartyNo, partyName, party.GetUnitList());
        }

        void UpdatePartySaveResult(PartySaveResultModel resultModel)
        {
            var fetchOtherModel = GameRepository.GetGameFetchOther();

            var updatedFetchOtherModel = fetchOtherModel with
            {
                UserPartyModels = fetchOtherModel.UserPartyModels.Update(resultModel.Parties)
            };

            GameManagement.SaveGameFetchOther(updatedFetchOtherModel);
        }
    }
}
