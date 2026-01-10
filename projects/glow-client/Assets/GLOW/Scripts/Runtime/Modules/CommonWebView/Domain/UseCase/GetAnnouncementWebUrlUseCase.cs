using Cysharp.Text;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Resolvers;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Modules.CommonWebView.Domain.Model;
using Zenject;

namespace GLOW.Modules.CommonWebView.Domain.UseCase
{
    public class GetAnnouncementWebUrlUseCase
    {
        [Inject] IWebCdnHostResolver WebCdnHostResolver { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public CommonWebResultModel GetAnnouncementWebUrl(AnnouncementContentsUrl announcementUrl)
        {
            var baseUrl = WebCdnHostResolver.Resolve().Uri;
            var cacheBusterValue = TimeProvider.Now.Ticks.ToString();
            // キャッシュ無効化のためにクエリパラメータを追加
            var url = ZString.Format("{0}/{1}?v={2}", baseUrl, announcementUrl.Value, cacheBusterValue);
            return new CommonWebResultModel(url, "お知らせ");
        }
    }
}