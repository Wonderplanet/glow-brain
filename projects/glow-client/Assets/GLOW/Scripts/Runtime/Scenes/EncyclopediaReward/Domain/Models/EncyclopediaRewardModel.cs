using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaReward.Domain.Models
{
    public record EncyclopediaRewardModel(
        EncyclopediaUnitGrade CurrentGrade,
        IReadOnlyList<EncyclopediaRewardListCellModel> ReleasedCells,
        IReadOnlyList<EncyclopediaRewardListCellModel> LockedCells);
}
