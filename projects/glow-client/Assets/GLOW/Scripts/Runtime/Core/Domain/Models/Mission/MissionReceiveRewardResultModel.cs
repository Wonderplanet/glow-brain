using System.Collections.Generic;

namespace GLOW.Core.Domain.Models.Mission
{
    public record MissionReceiveRewardResultModel(
        IReadOnlyList<UserMissionBonusPointModel> UserMissionBonusPointModels,
        IReadOnlyList<MissionRewardModel> MissionRewardModels,
        UserParameterModel UserParameterModel,
        IReadOnlyList<UserItemModel> UserItemModels,
        IReadOnlyList<UserUnitModel> UserUnitModels,
        UserLevelUpResultModel UserLevelUpModel,
        IReadOnlyList<UserConditionPackModel> ConditionPackModels)
    {
        public static MissionReceiveRewardResultModel Empty { get; } = new(
            new List<UserMissionBonusPointModel>(),
            new List<MissionRewardModel>(),
            UserParameterModel.Empty,
            new List<UserItemModel>(),
            new List<UserUnitModel>(),
            UserLevelUpResultModel.Empty,
            new List<UserConditionPackModel>());
    }
}
