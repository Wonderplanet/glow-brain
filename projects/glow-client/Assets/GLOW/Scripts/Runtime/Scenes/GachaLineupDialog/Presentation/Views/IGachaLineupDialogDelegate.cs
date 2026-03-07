namespace GLOW.Scenes.GachaLineupDialog.Presentation.Views
{
    public interface IGachaLineupDialogDelegate
    {
        void OnViewDidLoad();
        void OnClosed();
        void OnNormalRatioTabSelected();
        void OnSSRRatioTabSelected();
        void OnURRatioTabSelected();
        void OnPickupRatioTabSelected();
    }
}