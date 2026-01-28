using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.PvpRanking.Presentation.Views;
using Zenject;
namespace GLOW.Scenes.PvpRanking.Presentation.Presenters
{
    /// <summary>
    /// 決闘
    /// 　決闘ランキング
    /// 　　決闘ランキング画面画面
    /// </summary>
    public class PvpRankingPresenter : IPvpRankingViewDelegate
    {
        [Inject] PvpRankingViewController ViewController { get; }
        [Inject] PvpRankingViewController.Argument Argument { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }

        bool _isPrev = false;

        void IPvpRankingViewDelegate.OnViewDidLoad()
        {
            if (_isPrev)
            {
                ViewController.SetUpPrevRanking(Argument.PvpRankingViewModel.PrevRanking);
            }
            else
            {
                ViewController.SetUpCurrentRanking(Argument.PvpRankingViewModel.CurrentRanking);
            }
        }

        void IPvpRankingViewDelegate.OnHelpButtonTapped()
        {
            CommonToastWireFrame.ShowScreenCenterToast("ヘルプボタンが押されました|n|n遊び方表示予定");
        }

        void IPvpRankingViewDelegate.OnBackButtonTapped()
        {
            HomeViewNavigation.TryPop();
        }

        void IPvpRankingViewDelegate.OnCurrentRankingButtonTapped()
        {
            if (!_isPrev)
            {
                return;
            }

            _isPrev = false;
            ViewController.SetUpCurrentRanking(Argument.PvpRankingViewModel.CurrentRanking);
        }

        void IPvpRankingViewDelegate.OnPrevRankingButtonTapped()
        {
            if (_isPrev)
            {
                return;
            }

            _isPrev = true;
            ViewController.SetUpPrevRanking(Argument.PvpRankingViewModel.PrevRanking);
        }
    }
}
