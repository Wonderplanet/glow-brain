using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Domain.Models.Unit
{
    public record UnitGradeUpResultModel(
        UserUnitModel UserUnit,
        IReadOnlyList<UserItemModel> UserItems,
        IReadOnlyList<UserArtworkModel> UserArtworks,
        IReadOnlyList<UserArtworkFragmentModel> UserArtworkFragments,
        IReadOnlyList<RewardModel> RewardModels);
}
