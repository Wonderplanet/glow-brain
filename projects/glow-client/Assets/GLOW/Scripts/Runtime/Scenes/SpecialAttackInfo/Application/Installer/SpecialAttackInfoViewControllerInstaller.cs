using GLOW.Scenes.SpecialAttackInfo.Domain.UseCases;
using GLOW.Scenes.SpecialAttackInfo.Presentation.Presenters;
using GLOW.Scenes.SpecialAttackInfo.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.SpecialAttackInfo.Application.Installer
{
    public class SpecialAttackInfoViewControllerInstaller : Zenject.Installer
    {
        [Inject] SpecialAttackInfoViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            ApplicationLog.Log(nameof(SpecialAttackInfoViewControllerInstaller), "InstallBindings");

            Container.BindInstance(Argument).AsCached();
            Container.BindViewWithKernal<SpecialAttackInfoViewController>();
            Container.Bind<GetSpecialAttackInfoModelUseCase>().AsCached();
            Container.BindInterfacesTo<SpecialAttackInfoPresenter>().AsCached();
        }
    }
}
