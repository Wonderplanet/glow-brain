namespace GLOW.Scenes.GachaDetailDialog.Presentation.Views
{
    internal interface IGachaDetailViewDelegate
    {
        void OnViewDidLoad();
        void OnClosed();
        void SwitchShowAnnouncementWebView();
        void SwitchShowCautionWebView();
    }
}
