using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.Mission.Presentation.ViewModel.DailyBonus
{
    public class DailyBonusMissionCellViewModel : IDailyBonusMissionCellViewModel
    {
        public MasterDataId DailyBonusMissionId { get; }
        public MissionStatus MissionStatus { get; }
        public LoginDayCount LoginDayCount { get; }
        public IReadOnlyList<PlayerResourceIconViewModel> PlayerResourceIconViewModels { get; }

        public DailyBonusMissionCellViewModel(MasterDataId dailyBonusMissionId, MissionStatus missionStatus, LoginDayCount loginDayCount, IReadOnlyList<PlayerResourceIconViewModel> playerResourceIconViewModels)
        {
            DailyBonusMissionId = dailyBonusMissionId;
            MissionStatus = missionStatus;
            LoginDayCount = loginDayCount;
            PlayerResourceIconViewModels = playerResourceIconViewModels;
        }
    }
}
