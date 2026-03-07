using GLOW.Scenes.PvpNewSeasonStart.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.PvpNewSeasonStart.Presentation.Presenters
{
    public class PvpNewSeasonStartPresenter : IPvpNewSeasonStartViewDelegate
    {
        [Inject] PvpNewSeasonStartViewController ViewController { get; }
        [Inject] PvpNewSeasonStartViewController.Argument Argument { get; }

        public void ViewDidLoad()
        {
            ViewController.SetUp(Argument.ViewModel);
        }

        public void OnCloseButtonTapped()
        {
            ViewController.Dismiss();
        }
    }
}

