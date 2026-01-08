using GLOW.Modules.UnitAvatarPageView.Domain.UseCases;
using GLOW.Modules.UnitAvatarPageView.Presentation.Presenters;
using GLOW.Modules.UnitAvatarPageView.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Modules.UnitAvatarPageView.Application.Views
{
    public class EnemyAvatarPageViewControllerInstaller : Installer
    {
        [Inject] IUnitAvatarPageViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindViewWithKernal<UnitAvatarPageViewController>();
            Container.BindInterfacesTo<UnitAvatarPagePresenter>().AsCached();
            Container.BindInterfacesTo<GetEnemyAvatarImageUseCase>().AsCached();
            Container.BindInstance(Argument).AsCached();
        }
    }
}
