using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.Repositories;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Presenters;
using GLOW.Scenes.Home.Presentation.Views.HomeMainKomaSetting;
using GLOW.Scenes.HomeMainKomaSettingUnitSelect.Application;
using GLOW.Scenes.HomeMainKomaSettingUnitSelect.Presentation;
using Zenject;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;

namespace GLOW.Scenes.HomeMainKomaSetting.Application
{
    public class HomeMainKomaSettingViewInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<HomeMainKomaSettingViewController>();
            Container.BindInterfacesTo<HomeMainKomaSettingPresenter>().AsCached();

            Container.Bind<HomeMainKomaSettingUseCase>().AsCached();
            Container.Bind<HomeMainKomaSettingApplyUseCase>().AsCached();
            Container.Bind<SaveCurrentMstKomaPatternUseCase>().AsCached();
        }
    }
}
