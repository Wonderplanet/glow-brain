using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.AdventBattleRewardList.Presentation.ViewModel
{
    public interface IAdventBattlePersonalCellViewModel
    {
        MasterDataId Id { get; }
        IReadOnlyList<PlayerResourceIconViewModel> Rewards { get; }
        public AdventBattleRewardCategory RewardCategory { get; }
    }
}