using WonderPlanet.ErrorCoordinator;
using WPFramework.Application.ErrorHandle;
using WPFramework.Exceptions.Mappers;
using Zenject;

namespace WPFramework.Application.Installers
{
    internal sealed class ErrorHandleInstaller : Installer
    {
        public override void InstallBindings()
        {
            // NOTE: クライアントエラーハンドリング
            Container.BindInterfacesTo<ClientErrorHandler>().AsCached();
            Container.BindInterfacesTo<DefaultErrorLogHandler>().AsCached();
            Container.BindInterfacesTo<ErrorCoordinator>().AsCached();

            // NOTE: IOExceptionの変換処理をインストール
            Container.Bind<IOExceptionMapper>().AsCached();
        }
    }
}
