namespace GLOW.Scenes.GachaRatio.Presentation.Views
{
    public interface IGachaRatioDialogViewDelegate
    {
        void OnViewDidLoad();
        void OnClosed();
        void OnNormalRatioTabSelected();
        void OnSSRRatioTabSelected();
        void OnURRatioTabSelected();
        void OnPickupRatioTabSelected();
    }
}
