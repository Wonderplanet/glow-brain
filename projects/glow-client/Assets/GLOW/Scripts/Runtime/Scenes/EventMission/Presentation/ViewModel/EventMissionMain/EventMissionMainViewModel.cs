using GLOW.Scenes.EventMission.Presentation.ViewModel.EventAchievementMission;
using GLOW.Scenes.EventMission.Presentation.ViewModel.EventDailyBonus;

namespace GLOW.Scenes.EventMission.Presentation.ViewModel.EventMissionMain
{
    public record EventMissionMainViewModel(
        EventAchievementMissionViewModel EventAchievementMissionViewModel,
        EventDailyBonusViewModel EventDailyBonusViewModel);
}
