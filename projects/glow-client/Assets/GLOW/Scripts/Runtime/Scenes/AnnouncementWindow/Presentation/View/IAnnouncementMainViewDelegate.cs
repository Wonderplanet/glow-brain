namespace GLOW.Scenes.AnnouncementWindow.Presentation.View
{
    public interface IAnnouncementMainViewDelegate
    {
        void OnViewDidLoad();
        void OnEventTabSelected();
        void OnOperationTabSelected();
        void OnCloseSelected();
    }
}