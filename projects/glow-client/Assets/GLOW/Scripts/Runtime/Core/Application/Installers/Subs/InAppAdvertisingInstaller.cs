using GLOW.Core.Domain.UseCases;
using GLOW.Core.Modules.Advertising;
using GLOW.Core.Modules.Advertising.AdfurikunAgent;
using GLOW.Core.Modules.Advertising.AppIdResolver;
using GLOW.Core.Presentation.Views.InAppAdvertisingConfirmView;
using GLOW.Core.Presentation.Wireframe;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Core.Application.Installers.Subs
{
    internal sealed class InAppAdvertisingInstaller : Installer
    {
        public override void InstallBindings()
        {
            // NOTE: パートーナーのインスタンスを作成する

            Container.BindInterfacesTo<AdfurikunAdvertisingAgent>().AsCached();
            Container.BindInterfacesTo<AdvertisingManager>().AsCached();
            Container.Bind<InAppAdvertisingWireframe>().AsCached();
            Container.BindViewFactoryInfo<InAppAdvertisingConfirmViewController, InAppAdvertisingConfirmViewInstaller>();
            Container.Bind<InAppAdvertisingUseCase>().AsCached();

            // NOTE: 広告ユニット
            //       環境毎に適切な広告ユニットを解決するためのResolverたち
#if GLOW_DEBUG
            Container.BindInterfacesTo<DevelopGlowRewardAppIdResolver>().AsCached();
#else
            Container.BindInterfacesTo<ProductionGlowRewardAppIdResolver>().AsCached();
#endif
        }
    }
}
