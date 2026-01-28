using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.QuestContentTop.Domain
{
    public class UpdateBadgeForContentTopUseCase
    {
        [Inject] IGameService GameService { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        
        public async UniTask<IReadOnlyDictionary<MasterDataId, NotificationBadge>> UpdateBadgeAndMaintenance(
            CancellationToken cancellationToken)
        {
            var result = await GameService.Badge(cancellationToken);
            
            // レスポンスの内容をGameFetch、GameFetchOtherに反映(副作用)
            ApplyUpdateFetchModel(result.Badge, result.MngContentCloses);

            var eventMissionBadgeModels = result.Badge.UnreceivedMissionEventRewardCounts;
            var eventMissionBadgeDictionary = eventMissionBadgeModels.ToDictionary(
                pair => pair.MstEventId,
                pair => new NotificationBadge(!pair.UnreceivedMissionEventRewardCount.IsZero())
            );
            
            return eventMissionBadgeDictionary;
        }

        void ApplyUpdateFetchModel(
            BadgeModel badgeModel, 
            IReadOnlyList<MngContentCloseModel> mngContentCloseModels)
        {
            // バッジ
            var fetchModel = GameRepository.GetGameFetch();
            var updatedFetchModel = fetchModel with
            {
                BadgeModel = badgeModel
            };

            // 部分メンテ
            var fetchOtherModel = GameRepository.GetGameFetchOther();
            var updatedFetchOtherModel = fetchOtherModel with
            {
                MngContentCloseModels = mngContentCloseModels
            };

            GameManagement.SaveGameUpdateAndFetch(updatedFetchModel, updatedFetchOtherModel);
        }
    }
}