using System.Collections.Generic;

namespace GLOW.Scenes.IdleIncentiveTop.Domain.Models
{
    public record IdleIncentiveRewardListModel(IReadOnlyList<IdleIncentiveRewardListCellModel> Rewards);
}
