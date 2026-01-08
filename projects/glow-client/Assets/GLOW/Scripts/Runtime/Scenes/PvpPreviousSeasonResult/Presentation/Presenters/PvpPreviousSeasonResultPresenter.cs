using GLOW.Scenes.PvpPreviousSeasonResult.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.PvpPreviousSeasonResult.Presentation.Presenters
{
    public class PvpPreviousSeasonResultPresenter : IPvpPreviousSeasonResultViewDelegate
    {
        [Inject] PvpPreviousSeasonResultViewController ViewController { get; }
        [Inject] PvpPreviousSeasonResultViewController.Argument Argument { get; }

        void IPvpPreviousSeasonResultViewDelegate.ViewDidLoad()
        {
            ViewController.SetUp(Argument.ViewModel);
        }

        void IPvpPreviousSeasonResultViewDelegate.OnCloseButtonClicked()
        {
            ViewController.Dismiss();
        }
    }
}
