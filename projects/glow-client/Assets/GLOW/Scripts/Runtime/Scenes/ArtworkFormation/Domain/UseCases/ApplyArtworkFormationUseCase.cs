using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models.Party;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using Zenject;

namespace GLOW.Scenes.ArtworkFormation.Domain.UseCases
{
    public class ApplyArtworkFormationUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPartyService PartyService { get; }
        [Inject] IGameManagement GameManagement { get; }
        
        public async UniTask ApplyArtworkFormation(CancellationToken cancellationToken, IReadOnlyList<MasterDataId> mstArtworkIds)
        {
            // 編成が同じ場合はスキップ（EmptyのIDを除外して比較）
            var currentMstArtworkIds = GameRepository.GetGameFetchOther().UserArtworkPartyModel.GetArtworkList()
                .Where(id => !id.IsEmpty())
                .ToList();
            if (currentMstArtworkIds.SequenceEqual(mstArtworkIds)) return;
            
            // APIで保存
            var result = await PartyService.ArtworkSave(cancellationToken, mstArtworkIds);
            
            // ローカルの原画編成を保存
            UpdateArtworkPartySaveResult(result);
        }

        void UpdateArtworkPartySaveResult(ArtworkPartySaveResultModel resultModel)
        {
            var fetchOtherModel = GameRepository.GetGameFetchOther();

            var updatedFetchOtherModel = fetchOtherModel with
            {
                UserArtworkPartyModel = resultModel.UserArtworkParty
            };

            GameManagement.SaveGameFetchOther(updatedFetchOtherModel);
        }
    }
}
