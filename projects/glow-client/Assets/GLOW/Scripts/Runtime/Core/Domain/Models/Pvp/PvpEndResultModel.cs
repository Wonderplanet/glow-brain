using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record PvpEndResultModel(
        UserPvpStatusModel UsrPvpStatus,
        PvpEndResultBonusPointModel BonusPointModel,
        IReadOnlyList<PvpRewardModel> RewardModels,
        UserParameterModel ParameterModel,
        IReadOnlyList<UserItemModel> UsrItems,
        IReadOnlyList<UserEmblemModel> UsrEmblems)
    {
        public static PvpEndResultModel Empty { get; } = new(
            UserPvpStatusModel.Empty,
            PvpEndResultBonusPointModel.Empty,
            new List<PvpRewardModel>(),
            UserParameterModel.Empty,
            new List<UserItemModel>(),
            new List<UserEmblemModel>()
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
