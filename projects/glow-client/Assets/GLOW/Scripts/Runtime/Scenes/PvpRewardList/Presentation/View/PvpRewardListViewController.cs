using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.PvpRewardList.Presentation.Enum;
using GLOW.Scenes.PvpRewardList.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.PvpRewardList.Presentation.View
{
    /// <summary>
    /// 043-01_報酬一覧画面
    /// </summary>
    public class PvpRewardListViewController : UIViewController<PvpRewardListView>
    {
        [Inject] IPvpRewardListViewDelegate ViewDelegate { get; }
        
        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ActualView.InitializeRewardListView();
            ViewDelegate.OnViewDidLoad();
        }
        
        public void SetUpPvpRewardList(
            PvpRewardListViewModel viewModel,
            PvpRewardListTabType tabType)
        {
            SetUpRemainingTime(viewModel.RemainingTimeSpan);
            ActualView.SetUpRewardListView(
                viewModel,
                OnRewardIconTapped,
                tabType);
        }
        
        public void SetUpRemainingTime(RemainingTimeSpan remainingTimeSpan)
        {
            ActualView.SetupRemainingTime(remainingTimeSpan);
        }
        
        void OnRewardIconTapped(PlayerResourceIconViewModel viewModel)
        {
            ViewDelegate.OnItemIconTapped(viewModel);
        }
        
        [UIAction]
        void OnBackButtonTapped()
        {
            ViewDelegate.OnBackButtonTapped();
        }
        
        [UIAction]
        void OnRankingTabButtonTapped()
        {
            ViewDelegate.OnRankingTabButtonTapped();
        }

        [UIAction]
        void OnRankRewardTabButtonTapped()
        {
            ViewDelegate.OnRankRewardTabButtonTapped();
        }
        
        [UIAction]
        void OnTotalScoreTabButtonTapped()
        {
            ViewDelegate.OnTotalScoreTabButtonTapped();
        }
    }
}