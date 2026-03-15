using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using Zenject;

namespace GLOW.Scenes.ArtworkPanelMission.Domain.Applier
{
    public class ArtworkPanelMissionReceivedStatusApplier : IArtworkPanelMissionReceivedStatusApplier
    {
        [Inject] IMissionOfArtworkPanelRepository MissionOfArtworkPanelRepository { get; }
        
        IReadOnlyList<UserMissionLimitedTermModel> IArtworkPanelMissionReceivedStatusApplier.UpdateReceivedArtworkPanelMissions(
            IReadOnlyList<MissionReceiveRewardModel> receivedMissionModels)
        {
            var userMissionModels = MissionOfArtworkPanelRepository.GetUserMissionLimitedTermModels();
            // 受け取った期間限定ミッションのId
            var receivedLimitedMissionIdSets =
                receivedMissionModels
                    .Where(model => model.UnreceivedRewardReason == UnreceivedRewardReasonType.None)
                    .Where(model => model.MissionType == MissionType.LimitedTerm)
                    .Select(model => model.MstMissionId)
                    .ToHashSet();
            
            userMissionModels = UpdateLimitedTermMissionModels(
                userMissionModels,
                receivedLimitedMissionIdSets);
            MissionOfArtworkPanelRepository.SetUserMissionLimitedTermModels(userMissionModels);
            
            return userMissionModels;
        }
        
        IReadOnlyList<UserMissionLimitedTermModel> UpdateLimitedTermMissionModels(
            IReadOnlyList<UserMissionLimitedTermModel> userMissionLimitedTermModels,
            HashSet<MasterDataId> receivedMissionIdSets)
        {
            var updatedUserMissionLimitedTermModels = new List<UserMissionLimitedTermModel>();
            foreach (var model in userMissionLimitedTermModels)
            {
                // HashSetに含まれていれば受け取り済み
                var isReceived = receivedMissionIdSets.Contains(model.MstMissionLimitedTermId);

                if (!isReceived)
                {
                    updatedUserMissionLimitedTermModels.Add(model);
                    continue;
                }

                var updatedModel = UpdateLimitedTermMissionModelStatus(model, MissionStatus.Received);
                updatedUserMissionLimitedTermModels.Add(updatedModel);
            }

            return updatedUserMissionLimitedTermModels;
        }
        
        UserMissionLimitedTermModel UpdateLimitedTermMissionModelStatus(
            UserMissionLimitedTermModel userMissionModel,
            MissionStatus missionStatus)
        {
            return userMissionModel with
            {
                IsCleared = missionStatus.IsClearedStatus(),
                IsReceivedReward = missionStatus.IsReceivedStatus()
            };
        }
    }
}