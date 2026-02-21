using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Resolvers;
using GLOW.Modules.CommonWebView.Domain.Model;
using UnityEngine;
using Zenject;

namespace GLOW.Modules.CommonWebView.Domain.UseCase
{
    public class GetCommonWebUrlUseCase
    {
        [Inject] IWebCdnHostResolver WebCdnHostResolver { get; }

        public CommonWebResultModel GetCommonWebUrl(WebViewShownContentType type)
        {
            var baseUrl = WebCdnHostResolver.Resolve().Uri;

            switch (type)
            {
                case WebViewShownContentType.TermsOfService:
                {
                    string url = $"https://legal.bandainamcoent.co.jp/terms/nejp";
                    string title = "利用規約";
                    return new CommonWebResultModel(url, title);
                }
                case WebViewShownContentType.SpecificCommerce:
                {
#if UNITY_IOS
                    string url = $"{baseUrl}/policies/specific_commerce/sc_v1_ja_ios.html";
#elif UNITY_ANDROID
                    string url = $"{baseUrl}/policies/specific_commerce/sc_v1_ja_android.html";
#else
                    string url = $"{baseUrl}/policies/specific_commerce/sc_v1_ja_ios.html";
#endif
                    string title = "特定商取引法に基づく表示";
                    Debug.Log(url);
                    return new CommonWebResultModel(url, title);
                }
                case WebViewShownContentType.FundsSettlement:
                {
                    string url = $"{baseUrl}/policies/funds_settlement/fs_v1_ja.html";
                    string title = "資金決済法に基づく表示";
                    Debug.Log(url);
                    return new CommonWebResultModel(url, title);
                }
                case WebViewShownContentType.PluginLicenses:
                {
                    string url = $"{baseUrl}/policies/plugin_licences/pl_v1_ja.html";
                    string title = "コピーライト";
                    return new CommonWebResultModel(url, title);
                }
                default:
                    return new CommonWebResultModel("", "");
            }

            // url = "https://develop-web.seed.nappers.jp/policies/privacy_policy/pp_v1_ja.html";
            // title = "プライバシーポリシー";
        }
    }
}
