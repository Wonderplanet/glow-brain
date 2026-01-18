using GLOW.Modules.GameOption.Domain.UseCases;
using GLOW.Scenes.HomeMenuSetting.Domain.UseCase;
using GLOW.Scenes.HomeMenuSetting.Presentation.Presenter;
using GLOW.Scenes.HomeMenuSetting.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.HomeMenuSetting.Application.Views
{
    public class HomeMenuSettingViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<HomeMenuSettingViewController>();
            Container.BindInterfacesTo<HomeMenuSettingPresenter>().AsCached();
            
            Container.Bind<ShowHomeMenuSettingUseCase>().AsCached();
            Container.Bind<SwitchBgmGameOptionUseCase>().AsCached();
            Container.Bind<SwitchSeGameOptionUseCase>().AsCached();
            Container.Bind<SwitchDamageDisplayGameOptionUseCase>().AsCached();
            Container.Bind<SwitchPushOffGameOptionUseCase>().AsCached();
            Container.Bind<SetSpecialAttackCutInPlayTypeGameOptionUseCase>().AsCached();
        }
    }
}