namespace GLOW.Scenes.TitleMenu.Presentation
{
    public interface ITitleMenuViewDelegate
    {
        void OnViewDidLoad();
        void OnAnnouncement();
        void OnInquiry();
        void OnRepairData();
        void OnLinkAccount();
        void OnDeleteUserData();
    }
}
