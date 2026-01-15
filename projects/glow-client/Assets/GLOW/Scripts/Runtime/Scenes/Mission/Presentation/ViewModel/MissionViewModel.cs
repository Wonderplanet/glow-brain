using GLOW.Scenes.Mission.Presentation.ViewModel.AchievementMission;
using GLOW.Scenes.Mission.Presentation.ViewModel.DailyBonus;
using GLOW.Scenes.Mission.Presentation.ViewModel.DailyMission;
using GLOW.Scenes.Mission.Presentation.ViewModel.WeeklyMission;

namespace GLOW.Scenes.Mission.Presentation.ViewModel
{
    public class MissionViewModel : IMissionViewModel
    {
        public IAchievementMissionViewModel AchievementMissionViewModel { get; }
        
        public IDailyBonusMissionViewModel DailyBonusMissionViewModel { get; }
        
        public IDailyMissionViewModel DailyMissionViewModel { get; }
        
        public IWeeklyMissionViewModel WeeklyMissionViewModel { get; }
        
        public MissionViewModel(IAchievementMissionViewModel achievementMissionViewModel, IDailyBonusMissionViewModel dailyBonusMissionViewModel, IDailyMissionViewModel dailyMissionViewModel, IWeeklyMissionViewModel weeklyMissionViewModel)
        {
            AchievementMissionViewModel = achievementMissionViewModel;
            DailyBonusMissionViewModel = dailyBonusMissionViewModel;
            DailyMissionViewModel = dailyMissionViewModel;
            WeeklyMissionViewModel = weeklyMissionViewModel;
        }
    }
}