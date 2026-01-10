using GLOW.Scenes.PvpRanking.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.PvpRanking.Presentation.Views
{
    /// <summary>
    /// 決闘
    /// 　決闘ランキング
    /// 　　決闘ランキング画面
    /// </summary>
    public class PvpRankingViewController : UIViewController<PvpRankingView>
    {
        public record Argument(PvpRankingViewModel PvpRankingViewModel);

        [Inject] IPvpRankingViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public void SetUpCurrentRanking(PvpRankingElementViewModel viewModel)
        {
            ActualView.SetUpOtherUserComponents(viewModel.OtherUserViewModels);
            ActualView.SetUpMyselfUserComponent(viewModel.MyselfUserViewModel);
            ActualView.UpdateButtons(false);
            ActualView.SetUpCurrentRankingBand(false);
        }

        public void SetUpPrevRanking(PvpRankingElementViewModel viewModel)
        {
            ActualView.SetUpOtherUserComponents(viewModel.OtherUserViewModels);
            ActualView.SetUpMyselfUserComponent(viewModel.MyselfUserViewModel);
            ActualView.UpdateButtons(true);
            ActualView.SetUpCurrentRankingBand(true);
        }

        [UIAction]
        void OnHelpButtonTapped()
        {
            ViewDelegate.OnHelpButtonTapped();
        }

        [UIAction]
        void OnBackButtonTapped()
        {
            ViewDelegate.OnBackButtonTapped();
        }

        [UIAction]
        void OnCurrentRankingButtonTapped()
        {
            ViewDelegate.OnCurrentRankingButtonTapped();
        }

        [UIAction]
        void OnPrevRankingButtonTapped()
        {
            ViewDelegate.OnPrevRankingButtonTapped();
        }
    }
}
