namespace GLOW.Scenes.MaintenanceDialog.Presentation.View
{
    public interface IMaintenanceDialogViewDelegate
    {
        void ViewDidLoad();
        void OnOpenSNSPage();
        void OnOpenAnnouncementView();
        void OnClose();
    }
}
