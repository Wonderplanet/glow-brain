using GLOW.Modules.UnitAvatarPageView.Domain.UseCases;
using GLOW.Modules.UnitAvatarPageView.Presentation.Presenters;
using GLOW.Modules.UnitAvatarPageView.Presentation.Views;
using GLOW.Scenes.GachaUnitAvatarPage.Presentation.Presenters;
using GLOW.Scenes.GachaUnitAvatarPage.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.GachaUnitAvatarPage.Application.Views
{
    public class GachaUnitAvatarPageViewControllerInstaller : Zenject.Installer
    {
        [Inject] IUnitAvatarPageViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindViewWithKernal<GachaUnitAvatarPageViewController>();
            Container.BindInterfacesTo<UnitAvatarPagePresenter>().AsCached();
            Container.BindInterfacesTo<GachaUnitAvatarPagePresenter>().AsCached();
            Container.BindInterfacesTo<GetUnitAvatarImageUseCase>().AsCached();
            Container.BindInstance(Argument).AsCached();
        }
    }
}
