using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Modules.Network;
using GLOW.Core.Domain.Resolvers;
using GLOW.Core.Domain.Services;
using WonderPlanet.ResourceManagement;
using Zenject;

namespace GLOW.Scenes.Splash.Domain.UseCase
{
    public class BuildEnvironmentUseCase
    {
        [Inject] IEnvironmentService EnvironmentService { get; }
        [Inject] IApiContextHostBuilder ApiContextHostBuilder { get; }
        [Inject] IBannerCdnHostResolver BannerCdnHostResolver { get; }
        [Inject] IBannerManagement BannerManagement { get; }

        public async UniTask BuildEnvironment(CancellationToken cancellationToken)
        {
            // 環境情報の取得
            await EnvironmentService.FetchEnvironment(cancellationToken);

            // NOTE: 一番最初の環境を取得する
            var environment = EnvironmentService.FindConnectionEnvironment();

            // NOTE: 環境情報をもとに各種設定を行う
            ApiContextHostBuilder.Build(environment);

            var assetCdnHost = BannerCdnHostResolver.Resolve();
            BannerManagement.Initialize(assetCdnHost.Uri);
        }
    }
}

