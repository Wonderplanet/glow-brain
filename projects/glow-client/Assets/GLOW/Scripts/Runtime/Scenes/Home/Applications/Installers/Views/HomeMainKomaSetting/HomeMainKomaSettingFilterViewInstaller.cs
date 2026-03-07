using GLOW.Scenes.Home.Domain.UseCases;
using Zenject;
using UIKit.ZenjectBridge;
using GLOW.Scenes.HomeMainKomaSettingFilter.Presentation;

namespace GLOW.Scenes.HomeMainKomaSettingFilter.Application
{
    public class HomeMainKomaSettingFilterViewInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<HomeMainKomaSettingFilterViewController>();
            Container.BindInterfacesTo<HomeMainKomaSettingFilterPresenter>().AsCached();
            Container.Bind<HomeMainKomaSettingFilterUseCase>().AsCached();
            Container.Bind<UpdateHomeMainKomaSettingFilterUseCase>().AsCached();
        }
    }
}
