using UnityHTTPLibrary;
using WPFramework.Constants.Zenject;
using Zenject;

namespace WPFramework.Application.Installers
{
    internal sealed class NetworkInstaller : Installer
    {
        public override void InstallBindings()
        {
            // NOTE: サーバー用のContextをインストール
            Container.Bind<ServerApi>().WithId(FrameworkInjectId.ServerApi.Game).AsCached();
            Container.Bind<ServerApi>().WithId(FrameworkInjectId.ServerApi.Cdn).AsCached();
            Container.Bind<ServerApi>().WithId(FrameworkInjectId.ServerApi.Asset).AsCached();
            Container.Bind<ServerApi>().WithId(FrameworkInjectId.ServerApi.System).AsCached();
            Container.Bind<ServerApi>().WithId(FrameworkInjectId.ServerApi.Mst).AsCached();
            Container.Bind<ServerApi>().WithId(FrameworkInjectId.ServerApi.Announcement).AsCached();
            Container.Bind<ServerApi>().WithId(FrameworkInjectId.ServerApi.Banner).AsCached();
            Container.Bind<ServerApi>().WithId(FrameworkInjectId.ServerApi.UnhandledGame).AsCached();
            Container.Bind<ServerApi>().WithId(FrameworkInjectId.ServerApi.Agreement).AsCached();
        }
    }
}
