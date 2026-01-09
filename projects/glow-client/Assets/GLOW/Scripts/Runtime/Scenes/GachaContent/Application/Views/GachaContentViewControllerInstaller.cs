using GLOW.Scenes.EncyclopediaUnitSpecialAttack.Application.Views;
using GLOW.Scenes.EncyclopediaUnitSpecialAttack.Presentation.Views;
using GLOW.Scenes.GachaContent.Domain;
using GLOW.Scenes.GachaContent.Domain.UseCases;
using GLOW.Scenes.GachaContent.Presentation.Presenters;
using GLOW.Scenes.GachaContent.Presentation.Views;
using GLOW.Scenes.GachaUnitAvatarPage.Application.Views;
using GLOW.Scenes.GachaUnitAvatarPage.Presentation.Views;
using GLOW.Scenes.UnitDetail.Application.Views;
using GLOW.Scenes.UnitDetail.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.GachaContent.Application.Views
{
    public class GachaContentViewControllerInstaller : Installer
    {
        [Inject] GachaContentViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.Bind<GachaContentUseCase>().AsCached();
            Container.BindViewWithKernal<GachaContentViewController>();
            Container.BindInterfacesTo<GachaContentPresenter>().AsCached();
            Container.BindInterfacesTo<GachaDisplayUnitModelFactory>().AsCached();
            Container.BindInstance(Argument);

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.Bind<GachaWireFrame.Presentation.Presenters.GachaWireFrame>().AsCached();
            Container.BindViewFactoryInfo<GachaUnitAvatarPageViewController, GachaUnitAvatarPageViewControllerInstaller>();
            Container.BindViewFactoryInfo<UnitDetailViewController, UnitDetailViewControllerInstaller>();
            Container.BindViewFactoryInfo<UnitSpecialAttackPreviewViewController, UnitSpecialAttackPreviewViewControllerInstaller>();
        }
    }
}
