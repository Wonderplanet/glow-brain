using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.Mission.Presentation.ViewModel.DailyBonus
{
    public class DailyBonusTotalTypeMissionCellViewModel : IDailyBonusTotalTypeMissionCellViewModel
    {
        public MasterDataId DailyBonusMissionId { get; }
        public MissionStatus MissionStatus { get; }
        public IReadOnlyList<PlayerResourceIconViewModel> PlayerResourceIconViewModels { get; }
        public LoginDayCount LoginDayCount { get; }
        
        public DailyBonusTotalTypeMissionCellViewModel(MasterDataId dailyBonusMissionId, MissionStatus missionStatus, IReadOnlyList<PlayerResourceIconViewModel> playerResourceIconViewModels, LoginDayCount loginDayCount)
        {
            DailyBonusMissionId = dailyBonusMissionId;
            MissionStatus = missionStatus;
            PlayerResourceIconViewModels = playerResourceIconViewModels;
            LoginDayCount = loginDayCount;
        }
    }
}