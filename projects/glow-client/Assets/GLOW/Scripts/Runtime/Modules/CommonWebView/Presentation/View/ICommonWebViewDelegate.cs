namespace GLOW.Modules.CommonWebView.Presentation.View
{
    public interface ICommonWebViewDelegate
    {
        void OnViewDidLoad();
        void OnWebViewCallBack(string msg);
        void OnWebViewHooked(string msg);
    }
}
