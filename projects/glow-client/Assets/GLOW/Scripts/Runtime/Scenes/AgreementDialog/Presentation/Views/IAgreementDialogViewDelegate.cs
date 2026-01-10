namespace GLOW.Scenes.AgreementDialog.Presentation.Views
{
    public interface IAgreementDialogViewDelegate
    {
        void OnViewDidLoad();
        void OnWebViewHooked(string url);
        void OnWebViewError();
    }
}
