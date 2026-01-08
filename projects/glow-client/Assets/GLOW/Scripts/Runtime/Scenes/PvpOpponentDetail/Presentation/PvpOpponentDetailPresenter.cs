using GLOW.Scenes.PvpOpponentDetail.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.PvpOpponentDetail.Presentation
{
    public class PvpOpponentDetailPresenter : IPvpOpponentDetailViewDelegate
    {
        [Inject] PvpOpponentDetailViewController ViewController { get; }
        [Inject] PvpOpponentDetailViewController.Argument Argument { get; }

        void IPvpOpponentDetailViewDelegate.OnViewDidLoad()
        {
            ViewController.SetUp(Argument.PvpTopOpponentViewModel);
        }
    }
}
