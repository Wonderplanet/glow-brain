using System.Collections.Generic;

namespace GLOW.Scenes.IdleIncentiveTop.Presentation.ViewModels
{
    public record IdleIncentiveRewardListViewModel(IReadOnlyList<IdleIncentiveRewardListCellViewModel> Rewards);
}
