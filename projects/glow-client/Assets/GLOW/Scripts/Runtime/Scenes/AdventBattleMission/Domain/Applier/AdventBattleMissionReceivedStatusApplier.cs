using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Extensions;
using GLOW.Scenes.AdventBattleMission.Domain.Model;
using Zenject;

namespace GLOW.Scenes.AdventBattleMission.Domain.Applier
{
    public class AdventBattleMissionReceivedStatusApplier : IAdventBattleMissionReceivedStatusApplier
    {
        [Inject] IMissionOfAdventBattleRepository MissionOfAdventBattleRepository { get; }

        AdventBattleMissionAppliedModel IAdventBattleMissionReceivedStatusApplier.UpdateReceivedAdventBattleMission(
            MissionType missionType,
            MasterDataId receivedMissionId)
        {
            var userMissionEventModels = MissionOfAdventBattleRepository.GetUserMissionEventModels();
            var userMissionLimitedTermModels = MissionOfAdventBattleRepository.GetUserMissionLimitedTermModels();

            if (missionType == MissionType.Event)
            {
                userMissionEventModels = UpdateEventMissionModel(userMissionEventModels, receivedMissionId);
                MissionOfAdventBattleRepository.SetUserMissionEventModels(userMissionEventModels);
            }
            else if (missionType == MissionType.LimitedTerm)
            {
                userMissionLimitedTermModels = UpdateLimitedTermMissionModel(userMissionLimitedTermModels, receivedMissionId);
                MissionOfAdventBattleRepository.SetUserMissionLimitedTermModels(userMissionLimitedTermModels);
            }

            return new AdventBattleMissionAppliedModel(userMissionEventModels, userMissionLimitedTermModels);
        }

        AdventBattleMissionAppliedModel IAdventBattleMissionReceivedStatusApplier.UpdateReceivedAdventBattleMissions(
            IReadOnlyList<MissionReceiveRewardModel> receivedMissionModels)
        {
            var userMissionEventModels = MissionOfAdventBattleRepository.GetUserMissionEventModels();
            var userMissionLimitedTermModels = MissionOfAdventBattleRepository.GetUserMissionLimitedTermModels();

            // 受け取ったイベントミッションのId
            var receivedEventMissionIdSets =
                receivedMissionModels
                    .Where(data => data.UnreceivedRewardReason == UnreceivedRewardReasonType.None)
                    .Where(data => data.MissionType == MissionType.Event)
                    .Select(data => data.MstMissionId).ToHashSet();

            // 受け取った期間限定ミッションのId
            var receivedLimitedMissionIdSets =
                receivedMissionModels
                    .Where(data => data.UnreceivedRewardReason == UnreceivedRewardReasonType.None)
                    .Where(data => data.MissionType == MissionType.LimitedTerm)
                    .Select(data => data.MstMissionId).ToHashSet();

            // 受け取ったミッションのIdを持つモデルを更新
            userMissionEventModels = UpdateEventMissionModels(userMissionEventModels, receivedEventMissionIdSets);
            userMissionLimitedTermModels = UpdateLimitedTermMissionModels(
                userMissionLimitedTermModels,
                receivedLimitedMissionIdSets);

            MissionOfAdventBattleRepository.SetUserMissionEventModels(userMissionEventModels);
            MissionOfAdventBattleRepository.SetUserMissionLimitedTermModels(userMissionLimitedTermModels);

            return new AdventBattleMissionAppliedModel(userMissionEventModels, userMissionLimitedTermModels);
        }

        IReadOnlyList<UserMissionEventModel> UpdateEventMissionModel(
            IReadOnlyList<UserMissionEventModel> userMissionEventModels,
            MasterDataId receivedMissionId)
        {
            // 受け取ったミッションIDを持つモデルを取得
            var targetModel = userMissionEventModels
                .FirstOrDefault(model => model.MstMissionEventId == receivedMissionId,
                    UserMissionEventModel.Empty);

            // 含まれていない場合はそのまま返す
            if (targetModel.IsEmpty())  return userMissionEventModels;

            // 更新したモデルで置き換える
            var updatedModel = UpdateEventMissionModelStatus(targetModel, MissionStatus.Received);
            var updatedUserMissionEventModels = userMissionEventModels
                .Replace(targetModel, updatedModel);

            return updatedUserMissionEventModels;
        }

        IReadOnlyList<UserMissionLimitedTermModel> UpdateLimitedTermMissionModel(
            IReadOnlyList<UserMissionLimitedTermModel> userMissionLimitedTermModels,
            MasterDataId receivedMissionId)
        {
            // 受け取ったミッションIDを持つモデルを取得
            var targetModel = userMissionLimitedTermModels
                .FirstOrDefault(model => model.MstMissionLimitedTermId == receivedMissionId,
                    UserMissionLimitedTermModel.Empty);

            // 含まれていない場合はそのまま返す
            if (targetModel.IsEmpty())  return userMissionLimitedTermModels;

            // 更新したモデルで置き換える
            var updatedModel = UpdateLimitedTermMissionModelStatus(targetModel, MissionStatus.Received);
            var updatedUserMissionLimitedTermModels = userMissionLimitedTermModels
                .Replace(targetModel, updatedModel);

            return updatedUserMissionLimitedTermModels;
        }

        IReadOnlyList<UserMissionEventModel> UpdateEventMissionModels(
            IReadOnlyList<UserMissionEventModel> userMissionEventModels,
            HashSet<MasterDataId> receivedMissionIdSets)
        {
            var updatedUserMissionEventModels = new List<UserMissionEventModel>();
            foreach (var model in userMissionEventModels)
            {
                // HashSetに含まれていれば受け取り済み
                var isReceived = receivedMissionIdSets.Contains(model.MstMissionEventId);

                if (!isReceived)
                {
                    updatedUserMissionEventModels.Add(model);
                    continue;
                }

                var updatedModel = UpdateEventMissionModelStatus(model, MissionStatus.Received);
                updatedUserMissionEventModels.Add(updatedModel);
            }

            return updatedUserMissionEventModels;
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

        UserMissionEventModel UpdateEventMissionModelStatus(
            UserMissionEventModel userMissionEventModel,
            MissionStatus missionStatus)
        {
            var isClear = missionStatus.IsClearedStatus();
            var isReceivedReward = missionStatus.IsReceivedStatus();
            return userMissionEventModel with
            {
                IsCleared = isClear,
                IsReceivedReward = isReceivedReward
            };
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
