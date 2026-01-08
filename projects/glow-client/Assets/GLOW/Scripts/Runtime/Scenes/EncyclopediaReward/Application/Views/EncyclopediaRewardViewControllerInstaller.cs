using GLOW.Scenes.EncyclopediaEffectDialog.Application.Views;
using GLOW.Scenes.EncyclopediaEffectDialog.Presentation.Views;
using GLOW.Scenes.EncyclopediaReward.Domain.UseCases;
using GLOW.Scenes.EncyclopediaReward.Presentation.Presenters;
using GLOW.Scenes.EncyclopediaReward.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.EncyclopediaReward.Application.Views
{
    public class EncyclopediaRewardViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<EncyclopediaRewardViewController>();
            Container.BindInterfacesTo<EncyclopediaRewardPresenter>().AsCached();
            Container.Bind<GetEncyclopediaRewardUseCase>().AsCached();
            Container.Bind<ReceiveUnitEncyclopediaRewardUseCase>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<EncyclopediaEffectDialogViewController, EncyclopediaEffectDialogViewControllerInstaller>();
        }
    }
}
