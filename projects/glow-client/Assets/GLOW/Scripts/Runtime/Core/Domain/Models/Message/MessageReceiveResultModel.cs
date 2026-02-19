using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Domain.Models.Message
{
    public record MessageReceiveResultModel(
        IReadOnlyList<RewardModel> RewardModels,
        bool IsEmblemDuplicated,
        UserParameterModel UserParameterModel,
        IReadOnlyList<UserUnitModel> UserUnits,
        IReadOnlyList<UserItemModel> UserItems,
        IReadOnlyList<UserEmblemModel> UserEmblems,
        UserLevelUpResultModel UserLevel,
        IReadOnlyList<UserConditionPackModel> UserConditionPacks);
}
