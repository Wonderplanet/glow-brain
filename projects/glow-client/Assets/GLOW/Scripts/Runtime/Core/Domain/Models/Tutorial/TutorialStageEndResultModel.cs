using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Domain.Models.Tutorial
{
    public record TutorialStageEndResultModel(
        TutorialStatusModel TutorialStatusModel,
        UserParameterModel UserParameterModel,
        IReadOnlyList<StageRewardResultModel> Rewards,
        UserLevelUpResultModel UserLevelUp,
        IReadOnlyList<UserUnitModel> UserUnitModels,
        IReadOnlyList<UserItemModel> UserItemModels,
        IReadOnlyList<UserEmblemModel> UserEmblemModels)
    {
        public static TutorialStageEndResultModel Empty { get; } = new TutorialStageEndResultModel(
                TutorialStatusModel.Empty, 
                UserParameterModel.Empty,
                new List<StageRewardResultModel>(),
                UserLevelUpResultModel.Empty,
                new List<UserUnitModel>(),
                new List<UserItemModel>(),
                new List<UserEmblemModel>());
    }
}