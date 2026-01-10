using GLOW.Scenes.ItemBox.Presentation.Views;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.ItemBox.Presentation.Presenters
{
    public class RandomFragmentLineupPresenter : IRandomFragmentLineupViewDelegate
    {
        [Inject] RandomFragmentLineupViewController ViewController { get; }
        [Inject] RandomFragmentLineupViewController.Argument Argument { get; }

        void IRandomFragmentLineupViewDelegate.OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(RandomFragmentLineupPresenter), nameof(IRandomFragmentLineupViewDelegate.OnViewDidLoad));

            ViewController.Setup(Argument.Lineup);
        }

        void IRandomFragmentLineupViewDelegate.OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(RandomFragmentLineupPresenter), nameof(IRandomFragmentLineupViewDelegate.OnViewDidUnload));
        }

        void IRandomFragmentLineupViewDelegate.OnCloseSelected()
        {
            ViewController.Dismiss();
        }
    }
}
