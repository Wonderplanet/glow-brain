using GLOW.Scenes.PvpRewardList.Domain.Factory;
using GLOW.Scenes.PvpRewardList.Domain.UseCase;
using GLOW.Scenes.PvpRewardList.Presentation.Presenter;
using GLOW.Scenes.PvpRewardList.Presentation.View;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.PvpRewardList.Application.View
{
    public class PvpRewardListViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<PvpRewardListViewController>();
            Container.BindInterfacesTo<PvpRewardListPresenter>().AsCached();

            Container.BindInterfacesTo<PvpRewardModelFactory>().AsCached();
            Container.Bind<ShowPvpRewardListUseCase>().AsCached();
        }
    }
}