namespace GLOW.Scenes.IdleIncentiveQuickReward.Presentation.Views
{
    public interface IIdleIncentiveQuickReceiveWindowViewDelegate
    {
        void ViewDidLoad();
        void ViewDidAppear();
        void OnReceiveByAd();
        void OnReceiveAtDiamond();
        void OnClose();
    }
}
