namespace GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.Views
{
    public interface IArtworkFragmentAcquisitionViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidUnload();
        void OnCloseSelected();
        void OnBackButton();
    }
}
