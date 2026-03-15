using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Domain.Models.Mission
{
    public record MissionBulkReceiveRewardResultModel(
        IReadOnlyList<MissionReceiveRewardModel> MissionReceiveRewardModels,
        IReadOnlyList<MissionRewardModel> MissionRewardModels,
        IReadOnlyList<UserMissionAchievementModel> UserMissionAchievementModels,
        IReadOnlyList<UserMissionDailyModel> UserMissionDailyModels,
        IReadOnlyList<UserMissionWeeklyModel> UserMissionWeeklyModels,
        IReadOnlyList<UserMissionBeginnerModel> UserMissionBeginnerModels,
        IReadOnlyList<MissionEventModel> MissionEventModels,
        IReadOnlyList<UserMissionBonusPointModel> UserMissionBonusPointModels,
        UserParameterModel UserParameterModel,
        IReadOnlyList<UserItemModel> UserItemModels,
        IReadOnlyList<UserUnitModel> UserUnitModels,
        IReadOnlyList<UserArtworkModel> UserArtworkModels,
        IReadOnlyList<UserArtworkFragmentModel> UserArtworkFragmentModels,
        UserLevelUpResultModel UserLevelUpModel,
        IReadOnlyList<UserConditionPackModel> ConditionPackModels)
    {
        public static MissionBulkReceiveRewardResultModel Empty { get; } = new(
            new List<MissionReceiveRewardModel>(),
            new List<MissionRewardModel>(),
            new List<UserMissionAchievementModel>(),
            new List<UserMissionDailyModel>(),
            new List<UserMissionWeeklyModel>(),
            new List<UserMissionBeginnerModel>(),
            new List<MissionEventModel>(),
            new List<UserMissionBonusPointModel>(),
            UserParameterModel.Empty,
            new List<UserItemModel>(),
            new List<UserUnitModel>(),
            new List<UserArtworkModel>(),
            new List<UserArtworkFragmentModel>(),
            UserLevelUpResultModel.Empty,
            new List<UserConditionPackModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
