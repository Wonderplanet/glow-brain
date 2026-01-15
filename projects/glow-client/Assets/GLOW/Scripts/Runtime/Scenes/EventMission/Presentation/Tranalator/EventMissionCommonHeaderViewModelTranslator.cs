using GLOW.Scenes.EventMission.Domain.Model;
using GLOW.Scenes.EventMission.Presentation.ViewModel.EventMissionMain;

namespace GLOW.Scenes.EventMission.Presentation.Tranalator
{
    public class EventMissionCommonHeaderViewModelTranslator
    {
        public static EventMissionCommonHeaderViewModel ToEventMissionCommonHeaderViewModel(
            EventMissionCommonHeaderModel model)
        {
            return new EventMissionCommonHeaderViewModel(
                model.MstEventId,
                model.MissionBannerAssetPath,
                model.DailyBonusBannerAssetPath);
        }
    }
}