using GLOW.Scenes.EventBonusUnitList.Domain.UseCases;
using GLOW.Scenes.EventBonusUnitList.Presentation.Presenters;
using GLOW.Scenes.EventBonusUnitList.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.EventBonusUnitList.Application.Views
{
    public class EventBonusUnitListViewControllerInstaller : Installer
    {
        [Inject] EventBonusUnitListViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<EventBonusUnitListViewController>();
            Container.BindInterfacesTo<EventBonusUnitListUnitListPresenter>().AsCached();
            Container.Bind<ShowEventBonusUnitListUseCase>().AsCached();
            Container.BindInstance(Argument).AsCached();
        }
    }
}
