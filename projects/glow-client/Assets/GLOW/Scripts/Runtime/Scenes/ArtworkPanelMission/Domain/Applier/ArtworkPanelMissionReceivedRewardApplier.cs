using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.ArtworkPanelMission.Domain.Applier
{
    public class ArtworkPanelMissionReceivedRewardApplier : IArtworkPanelMissionReceivedRewardApplier
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        
        void IArtworkPanelMissionReceivedRewardApplier.UpdateGameFetchModel(
            UserParameterModel userParameterModel, 
            MasterDataId mstArtworkPanelMissionId,
            int receivableMissionEventCount)
        {
            var fetchModel = GameRepository.GetGameFetch();
            
            var badgeModel = fetchModel.BadgeModel;
            var updatedUnreceivedBadgeCounts = badgeModel.UnreceivedMissionArtworkPanelRewardCounts
                .ReplaceOrAdd(model => model.MstArtworkPanelMissionId == mstArtworkPanelMissionId, 
                    new MissionArtworkPanelRewardCountModel( 
                        mstArtworkPanelMissionId, 
                        new UnreceivedMissionRewardCount(receivableMissionEventCount)));
            var updatedBadgeModel = badgeModel with
            {
                UnreceivedMissionArtworkPanelRewardCounts = updatedUnreceivedBadgeCounts
            };

            var updatedFetchModel = fetchModel with
            {
                UserParameterModel = userParameterModel,
                BadgeModel = updatedBadgeModel
            };

            GameManagement.SaveGameFetch(updatedFetchModel);
        }

        void IArtworkPanelMissionReceivedRewardApplier.UpdateGameFetchOtherModel(
            MissionBulkReceiveRewardResultModel resultModel)
        {
            var fetchOtherModel = GameRepository.GetGameFetchOther();

            var newEmblemModels = resultModel.MissionRewardModels
                .Where(r => r.RewardModel.ResourceType == ResourceType.Emblem)
                .Select(r => new UserEmblemModel(r.RewardModel.ResourceId, NewEncyclopediaFlag.True))
                .ToList();
            
            var updatedGameFetchOther = fetchOtherModel with
            {
                UserItemModels = fetchOtherModel.UserItemModels.Update(resultModel.UserItemModels),
                UserUnitModels = fetchOtherModel.UserUnitModels.Update(resultModel.UserUnitModels),
                UserArtworkModels = fetchOtherModel.UserArtworkModels.Update(resultModel.UserArtworkModels),
                UserArtworkFragmentModels = fetchOtherModel.UserArtworkFragmentModels.Update(resultModel.UserArtworkFragmentModels),
                UserEmblemModel = fetchOtherModel.UserEmblemModel.Update(newEmblemModels),
                UserConditionPackModels = fetchOtherModel.UserConditionPackModels.Update(resultModel.ConditionPackModels),
            };

            GameManagement.SaveGameFetchOther(updatedGameFetchOther);
        }
    }
}