using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Domain.Models.Mission
{
    public record MissionEventDailyBonusUpdateResultModel(
        IReadOnlyList<MissionEventDailyBonusRewardModel> EventDailyBonusRewardModels,
        IReadOnlyList<UserMissionEventDailyBonusProgressModel> UserMissionEventDailyBonusProgressModels,
        UserParameterModel UserParameterModel,
        IReadOnlyList<UserUnitModel> UserUnitModels,
        IReadOnlyList<UserItemModel> UserItemModels,
        IReadOnlyList<UserEmblemModel> UserEmblemModels,
        UserLevelUpResultModel UserLevelUpResultModel, 
        IReadOnlyList<UserConditionPackModel> ConditionPackModels) 
    {
        public static MissionEventDailyBonusUpdateResultModel Empty { get; } = new(
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
