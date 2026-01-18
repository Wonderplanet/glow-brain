#if DEBUG
using GLOW.Core.Domain.Modules.Network;
using GLOW.Core.Domain.Resolvers;
using WonderPlanet.ResourceManagement;
using WPFramework.Debugs.Environment.Domain.Modules;
using Zenject;

namespace GLOW.Debugs.Environment.Domain.UseCases
{
    public class DebugBuildEnvironmentUseCase
    {
        [Inject] IDebugEnvironmentService DebugEnvironmentService { get; }
        [Inject] IApiContextHostBuilder ApiContextHostBuilder { get; }
        [Inject] IBannerCdnHostResolver BannerCdnHostResolver { get; }
        [Inject] IBannerManagement BannerManagement { get; }

        public void BuildEnvironment()
        {
            // NOTE: デバッグ用の環境を取得する
            var environment = DebugEnvironmentService.FindConnectionEnvironment();

            // NOTE: 環境情報をもとに各種設定を行う
            ApiContextHostBuilder.Build(environment);

            var assetCdnHost = BannerCdnHostResolver.Resolve();
            BannerManagement.Initialize(assetCdnHost.Uri);
        }
    }
}
#endif // / DEBUG
