using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Domain.Models.Unit
{
    public record UnitReceiveGradeUpRewardModel(
        UserUnitModel UserUnit,
        IReadOnlyList<UserArtworkModel> UserArtworks,
        IReadOnlyList<UserArtworkFragmentModel> UserArtworkFragments,
        IReadOnlyList<RewardModel> RewardModels);
}
