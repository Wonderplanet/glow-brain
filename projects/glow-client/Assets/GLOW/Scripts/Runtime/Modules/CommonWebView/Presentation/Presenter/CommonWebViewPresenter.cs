using GLOW.Core.Constants;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Resolvers;
using GLOW.Modules.CommonWebView.Domain.Model;
using GLOW.Modules.CommonWebView.Domain.UseCase;
using GLOW.Modules.CommonWebView.Presentation.View;
using GLOW.Modules.CommonWebView.Presentation.ViewModel;
using WonderPlanet.OpenURLExtension;
using Zenject;

namespace GLOW.Modules.CommonWebView.Presentation.Presenter
{
    public class CommonWebViewPresenter : ICommonWebViewDelegate
    {
        [Inject] GetCommonWebUrlUseCase GetCommonWebUrlUseCase { get; }
        [Inject] CommonWebViewController CommonWebViewController { get; }
        [Inject] GetAnnouncementWebUrlUseCase GetAnnouncementWebUrlUseCase { get; }
        [Inject] GetMyIdUseCase GetMyIdUseCase { get; }
        [Inject] CommonWebViewController.Argument Argument { get; }
        [Inject] IWebCdnHostResolver WebCdnHostResolver { get; }

        public void OnViewDidLoad()
        {
            if(Argument.Type == WebViewShownContentType.Announcement)
            {
                var resultModel = GetAnnouncementWebUrlUseCase.GetAnnouncementWebUrl(Argument.AnnouncementUrl);
                ShowCommonWebViewModel(resultModel);
            }
            else
            {
                var resultModel = GetCommonWebUrlUseCase.GetCommonWebUrl(Argument.Type);
                ShowCommonWebViewModel(resultModel);
            }
        }

        void ShowCommonWebViewModel(CommonWebResultModel resultModel)
        {
            var viewModel = new CommonWebViewModel(resultModel.Title, resultModel.Url, Argument.HookedPatternUrl);
            CommonWebViewController.SetViewModel(viewModel);
        }

        public void OnWebViewCallBack(string msg)
        {
            OpenUrlInBrowser(msg);
        }

        public void OnWebViewHooked(string msg)
        {
            if (msg.Contains(Credentials.UserQuestionnaireURL))
            {
                // ユーザーアンケートのURLを開く
                // URLのパラメータにMyIdを付与する
                var myId = GetMyIdUseCase.GetMyId();

                var paramUrl = msg + "?uid=" + myId;
                CustomOpenURL.OpenURL(paramUrl);
                return;
            }
            
            OpenUrlInBrowser(msg);
        }
        
        void OpenUrlInBrowser(string url)
        {
            var baseUrl = WebCdnHostResolver.Resolve().Uri;
            // aws以外のURLの場合はブラウザで開く
            if (!url.Contains(baseUrl) && url.Contains("http"))
            {
                CustomOpenURL.OpenURL(url);
            }
        }
    }
}
