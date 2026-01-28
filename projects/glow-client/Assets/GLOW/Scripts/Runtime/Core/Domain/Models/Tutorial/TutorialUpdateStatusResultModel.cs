using System.Collections.Generic;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Domain.Models.Tutorial
{
    public record TutorialUpdateStatusResultModel(
        List<UserGachaModel> UserGachaModels,
        UserIdleIncentiveModel UserIdleIncentiveModel,
        IReadOnlyList<MissionReceivedDailyBonusModel> MissionReceivedDailyBonusModel,
        IReadOnlyList<MissionEventDailyBonusRewardModel> MissionEventDailyBonusRewardModels,
        IReadOnlyList<UserMissionEventDailyBonusProgressModel> UserMissionEventDailyBonusProgressModels,
        UserParameterModel UserParameterModel,
        IReadOnlyList<UserUnitModel> UserUnitModels,
        IReadOnlyList<UserItemModel> UserItemModels,
        IReadOnlyList<UserEmblemModel> UserEmblemModels,
        UserLevelUpResultModel UserLevelUpResultModel,
        IReadOnlyList<UserConditionPackModel> UserConditionPackModels)
    {
        public static TutorialUpdateStatusResultModel Empty { get; } = new(
            new List<UserGachaModel>(),
            UserIdleIncentiveModel.Empty,
            new List<MissionReceivedDailyBonusModel>(),
            new List<MissionEventDailyBonusRewardModel>(),
            new List<UserMissionEventDailyBonusProgressModel>(),
            UserParameterModel.Empty,
            new List<UserUnitModel>(),
            new List<UserItemModel>(),
            new List<UserEmblemModel>(),
            UserLevelUpResultModel.Empty,
            new List<UserConditionPackModel>()); 
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}