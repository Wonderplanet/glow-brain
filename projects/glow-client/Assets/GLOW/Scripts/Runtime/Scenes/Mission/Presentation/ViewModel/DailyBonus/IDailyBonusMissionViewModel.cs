using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.Mission.Presentation.ViewModel.DailyBonus
{
    public interface IDailyBonusMissionViewModel
    {
        public IReadOnlyList<IDailyBonusMissionCellViewModel> DailyBonusMissionCellViewModels { get; }
        public UnreceivedMissionRewardCount UnreceivedMissionRewardCount { get; }
        public bool IsReceivableRewardExist();
    }
}
