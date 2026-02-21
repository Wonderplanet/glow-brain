using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.AdventBattleMission.Domain.Applier
{
    public class AdventBattleMissionReceivedRewardApplier : IAdventBattleMissionReceivedRewardApplier
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }

        void IAdventBattleMissionReceivedRewardApplier.UpdateGameFetchModel(
            UserParameterModel userParameterModel,
            int receivableMissionEventCount)
        {
            var fetchModel = GameRepository.GetGameFetch();
            var badgeModel = fetchModel.BadgeModel with
            {
                UnreceivedMissionAdventBattleRewardCount = new UnreceivedMissionRewardCount(receivableMissionEventCount)
            };

            var updatedFetchModel = fetchModel with
            {
                UserParameterModel = userParameterModel,
                BadgeModel = badgeModel
            };

            GameManagement.SaveGameFetch(updatedFetchModel);
        }

        void IAdventBattleMissionReceivedRewardApplier.UpdateGameFetchOtherModel(
            MissionReceiveRewardResultModel resultModel)
        {
            var fetchOtherModel = GameRepository.GetGameFetchOther();

            var newEmblemModels = resultModel.MissionRewardModels
                .Where(r => r.RewardModel.ResourceType == ResourceType.Emblem)
                .Select(r => new UserEmblemModel(r.RewardModel.ResourceId, NewEncyclopediaFlag.True))
                .ToList();

            var updatedGameFetchOther = fetchOtherModel with
            {
                UserConditionPackModels = fetchOtherModel.UserConditionPackModels.Update(resultModel.ConditionPackModels),
                UserEmblemModel = fetchOtherModel.UserEmblemModel.Update(newEmblemModels),
                UserItemModels = fetchOtherModel.UserItemModels.Update(resultModel.UserItemModels),
                UserUnitModels = fetchOtherModel.UserUnitModels.Update(resultModel.UserUnitModels)
            };

            GameManagement.SaveGameFetchOther(updatedGameFetchOther);
        }

        void IAdventBattleMissionReceivedRewardApplier.UpdateGameFetchOtherModel(
            MissionBulkReceiveRewardResultModel resultModel)
        {
            var fetchOtherModel = GameRepository.GetGameFetchOther();

            var newEmblemModels = resultModel.MissionRewardModels
                .Where(r => r.RewardModel.ResourceType == ResourceType.Emblem)
                .Select(r => new UserEmblemModel(r.RewardModel.ResourceId, NewEncyclopediaFlag.True))
                .ToList();

            var updatedGameFetchOther = fetchOtherModel with
            {
                UserConditionPackModels = fetchOtherModel.UserConditionPackModels.Update(resultModel.ConditionPackModels),
                UserEmblemModel = fetchOtherModel.UserEmblemModel.Update(newEmblemModels),
                UserItemModels = fetchOtherModel.UserItemModels.Update(resultModel.UserItemModels),
                UserUnitModels = fetchOtherModel.UserUnitModels.Update(resultModel.UserUnitModels)
            };

            GameManagement.SaveGameFetchOther(updatedGameFetchOther);
        }
    }
}
