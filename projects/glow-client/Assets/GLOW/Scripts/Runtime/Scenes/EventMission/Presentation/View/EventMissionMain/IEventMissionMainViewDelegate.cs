namespace GLOW.Scenes.EventMission.Presentation.View.EventMissionMain
{
    public interface IEventMissionMainViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidUnload();
        void OnEventDailyBonusTabSelected();
        void OnEventAchievementTabSelected();
        void OnBulkReceiveButtonSelected();
        void OnCloseSelected();
        void OnEscape();
    }
}