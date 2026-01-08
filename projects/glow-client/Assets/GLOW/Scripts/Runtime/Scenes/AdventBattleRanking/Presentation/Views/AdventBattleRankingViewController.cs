using GLOW.Scenes.AdventBattleRanking.Presentation.ViewModels;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using UIKit;
using Zenject;

namespace GLOW.Scenes.AdventBattleRanking.Presentation.Views
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-4_降臨バトルランキング
    /// 　　44-4-2_ランキング画面
    /// </summary>
    public class AdventBattleRankingViewController : UIViewController<AdventBattleRankingView>
    {
        public record Argument(AdventBattleRankingViewModel AdventBattleRankingViewModel);

        [Inject] IAdventBattleRankingViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public void SetUpRanking(AdventBattleRankingElementViewModel viewModel)
        {
            ActualView.SetUpTitle(viewModel.AdventBattleName);
            ActualView.SetUpOtherUserComponents(viewModel.OtherUserViewModels);
            ActualView.SetUpMyselfUserComponent(viewModel.MyselfUserViewModel);
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
    }
}
