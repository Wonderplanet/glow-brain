namespace GLOW.Scenes.PrivacyOptionDialog.Presentation.Views
{
    public interface IPrivacyOptionDialogViewDelegate
    {
        void OnViewDidLoad();
        void OnWebViewHooked(string url);
        void OnWebViewError(string msg);
    }
}
