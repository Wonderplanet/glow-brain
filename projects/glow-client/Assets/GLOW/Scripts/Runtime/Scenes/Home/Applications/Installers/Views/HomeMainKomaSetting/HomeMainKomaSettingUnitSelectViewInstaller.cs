using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Presenters;
using Zenject;
using UIKit.ZenjectBridge;
using GLOW.Scenes.HomeMainKomaSettingUnitSelect.Presentation;

namespace GLOW.Scenes.HomeMainKomaSettingUnitSelect.Application
{
    public class HomeMainKomaSettingUnitSelectViewInstaller : Installer
    {
        [Inject] HomeMainKomaSettingUnitSelectViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<HomeMainKomaSettingUnitSelectViewController>();

            Container.BindInterfacesTo<HomeMainKomaSettingUnitSelectPresenter>().AsCached();
            Container.Bind<HomeMainKomaSettingUnitSelectUseCase>().AsCached();
        }
    }
}
