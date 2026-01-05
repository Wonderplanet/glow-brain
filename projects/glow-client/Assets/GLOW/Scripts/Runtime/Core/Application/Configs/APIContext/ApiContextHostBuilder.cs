using System;
using GLOW.Core.Domain.Modules.Network;
using GLOW.Core.Domain.Resolvers;
using UnityHTTPLibrary;
using WPFramework.Constants.Zenject;
using WPFramework.Domain.Models;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Core.Application.Configs.APIContext
{
    public class ApiContextHostBuilder : IApiContextHostBuilder
    {
        [Inject(Id = FrameworkInjectId.ServerApi.Game)] ServerApi GameApiContext { get; }
        [Inject(Id = FrameworkInjectId.ServerApi.Asset)] ServerApi CdnContext { get; }
        [Inject(Id = FrameworkInjectId.ServerApi.System)] ServerApi SystemApiContext { get; }
        [Inject(Id = FrameworkInjectId.ServerApi.Mst)] ServerApi MstContext { get; }
        [Inject(Id = FrameworkInjectId.ServerApi.Announcement)] ServerApi AnnouncementContext { get; }
        [Inject(Id = FrameworkInjectId.ServerApi.Banner)] ServerApi BannerContext { get; }
        [Inject(Id = FrameworkInjectId.ServerApi.Agreement)] ServerApi AgreementContext { get; }
        [Inject] IAuthenticatorHostResolver AuthenticatorHostResolver { get; }
        [Inject] IApiHostResolver ApiHostResolver { get; }
        [Inject] IWebCdnHostResolver WebCdnHostResolver { get; }
        [Inject] IAssetCdnHostResolver AssetCdnHostResolver { get; }
        [Inject] IMstCdnHostResolver MstCdnHostResolver { get; }
        [Inject] IAnnouncementCdnHostResolver AnnouncementCdnHostResolver { get; }
        [Inject] IBannerCdnHostResolver BannerCdnHostResolver { get; }
        [Inject] IEnvironmentResolver EnvironmentResolver { get; }
        [Inject] IAgreementHostResolver AgreementHostResolver { get; }

        void IApiContextHostBuilder.Build(EnvironmentModel environment)
        {
            // NOTE: ホスト名の解決を行う
            AuthenticatorHostResolver.SetEnvironment(environment);
            ApiHostResolver.SetEnvironment(environment);
            AssetCdnHostResolver.SetEnvironment(environment);
            WebCdnHostResolver.SetEnvironment(environment);
            MstCdnHostResolver.SetEnvironment(environment);
            EnvironmentResolver.SetEnvironment(environment);
            AnnouncementCdnHostResolver.SetEnvironment(environment);
            BannerCdnHostResolver.SetEnvironment(environment);
            AgreementHostResolver.SetEnvironment(environment);

            // NOTE: 接続先の設定
            //       不正なUriだった場合はUriFormatExceptionが発生する
            GameApiContext.HostUri = new Uri(ApiHostResolver.Resolve().Uri);
            CdnContext.HostUri = new Uri(AssetCdnHostResolver.Resolve().Uri);
            SystemApiContext.HostUri = new Uri(AuthenticatorHostResolver.Resolve().Uri);
            MstContext.HostUri = new Uri(MstCdnHostResolver.Resolve().Uri);
            AnnouncementContext.HostUri = new Uri(AnnouncementCdnHostResolver.Resolve().Uri);
            BannerContext.HostUri = new Uri(BannerCdnHostResolver.Resolve().Uri);
            AgreementContext.HostUri = new Uri(AgreementHostResolver.Resolve().Uri);
        }
    }
}
