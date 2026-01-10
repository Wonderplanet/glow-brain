using GLOW.Scenes.ComeBackDailyBonus.Domain.UseCase;
using GLOW.Scenes.ComeBackDailyBonus.Presentation.Factory;
using GLOW.Scenes.ComebackDailyBonus.Presentation.Presenter;
using GLOW.Scenes.ComebackDailyBonus.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.ComebackDailyBonus.Application.Installers
{
    public class ComebackDailyBonusViewControllerInstaller : Installer
    {
        [Inject] ComebackDailyBonusViewController.Argument Argument { get; }
        
        public override void InstallBindings()
        {
            Container.BindInstance(Argument);
            Container.BindViewWithKernal<ComebackDailyBonusViewController>();
            Container.BindInterfacesTo<ComebackDailyBonusPresenter>().AsCached();

            Container.Bind<ShowComebackDailyBonusUseCase>().AsCached();
            Container.BindInterfacesTo<ComebackDailyBonusViewModelFactory>().AsCached();
        }
    }
}