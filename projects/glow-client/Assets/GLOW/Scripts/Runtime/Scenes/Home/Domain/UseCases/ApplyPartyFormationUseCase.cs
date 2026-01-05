using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Party;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class ApplyPartyFormationUseCase
    {
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPartyService PartyService { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IPreferenceRepository PreferenceRepository { get; }

        public IReadOnlyList<UserPartyCacheModel> GetNeedApplyPartyFormation()
        {
            // チュートリアル中の更新はチュートリアル内で行う
            if (!GameRepository.GetGameFetchOther().TutorialStatus.IsCompleted())
            {
                return new List<UserPartyCacheModel>();
            }
            
            var originalParties = GameRepository.GetGameFetchOther().UserPartyModels;
            return PartyCacheRepository.GetNeedsApplyParty(originalParties);
        }

        public async UniTask ApplyPartyFormation(CancellationToken cancellationToken, IReadOnlyList<UserPartyCacheModel> userPartyModels)
        {
            var tasks = userPartyModels
                .Select(model => Apply(cancellationToken, model))
                .ToList();
            await UniTask.WhenAll(tasks);
            var selectPartyNo = PreferenceRepository.SelectPartyNo;
            PartyCacheRepository.SetParties(GameRepository.GetGameFetchOther().UserPartyModels, selectPartyNo);
        }

        async UniTask Apply(CancellationToken cancellationToken, UserPartyCacheModel userPartyModel)
        {
            var result = await PartyService.Save(
                cancellationToken,
                userPartyModel.PartyNo,
                userPartyModel.PartyName,
                userPartyModel.GetUnitList());
            UpdatePartySaveResult(result);
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
