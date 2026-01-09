using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.AdventBattleRanking.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Views;
using Zenject;
namespace GLOW.Scenes.AdventBattleRanking.Presentation.Presenters
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-4_降臨バトルランキング
    /// 　　44-4-2_ランキング画面
    /// </summary>
    public class AdventBattleRankingPresenter : IAdventBattleRankingViewDelegate
    {
        [Inject] AdventBattleRankingViewController ViewController { get; }
        [Inject] AdventBattleRankingViewController.Argument Argument { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }

        void IAdventBattleRankingViewDelegate.OnViewDidLoad()
        {
            ViewController.SetUpRanking(Argument.AdventBattleRankingViewModel.CurrentRanking);
        }

        void IAdventBattleRankingViewDelegate.OnHelpButtonTapped()
        {
            CommonToastWireFrame.ShowScreenCenterToast("ヘルプボタンが押されました|n|n遊び方表示予定");
        }

        void IAdventBattleRankingViewDelegate.OnBackButtonTapped()
        {
            HomeViewNavigation.TryPop();
        }
    }
}
