using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaReward.Presentation.ViewModels
{
    public record EncyclopediaRewardViewModel(
        EncyclopediaUnitGrade CurrentGrade,
        IReadOnlyList<EncyclopediaRewardListCellViewModel> ReleasedCells,
        IReadOnlyList<EncyclopediaRewardListCellViewModel> LockedCells);
}
