using GLOW.Scenes.ItemBox.Presentation.Presenters;
using GLOW.Scenes.ItemBox.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.ItemBox.Application.Views
{
    public class RandomFragmentBoxLineupViewControllerInstaller : Installer
    {
        [Inject] RandomFragmentLineupViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument).AsCached();

            Container.BindViewWithKernal<RandomFragmentLineupViewController>();
            Container.BindInterfacesTo<RandomFragmentLineupPresenter>().AsCached();
        }
    }
}
