using GLOW.Scenes.EnhanceQuestScoreDetail.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.EnhanceQuestScoreDetail.Presentation.Views
{
    /// <summary>
    /// 45_強化クエスト
    /// 　42-5_1日N回強化クエスト
    /// 　　42-5-3-1_スコア獲得条件ダイアログ
    /// </summary>
    public class EnhanceQuestScoreDetailViewController : UIViewController<EnhanceQuestScoreDetailView>
    {
        [Inject] IEnhanceQuestScoreDetailViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public void Setup(EnhanceQuestScoreDetailViewModel viewModel)
        {
            ActualView.SetUpListView(viewModel.Cells);
        }

        [UIAction]
        void OnBackButtonTapped()
        {
            ViewDelegate.OnBackButtonTapped();
        }
    }
}
