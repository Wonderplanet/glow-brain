using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.AdventBattleRewardList.Presentation.ValueObject;
using GLOW.Scenes.AdventBattleRewardList.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.AdventBattleRewardList.Presentation.View
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-5_報酬
    /// 　　44-5-1_報酬一覧画面
    /// </summary>
    public class AdventBattleRewardListViewController : UIViewController<AdventBattleRewardListView>
    {
        public record Argument(MasterDataId MstAdventBattleId);
            
        [Inject] IAdventBattleRewardListViewDelegate ViewDelegate { get; }
        
        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public void SetupAdventBattleRewardList(
            AdventBattleRewardListViewModel viewModel,
            AdventBattleRewardListTabType tabType)
        {
            SetupRemainingTime(viewModel.RemainingTimeSpan);
            if(viewModel.AdventBattleType == AdventBattleType.ScoreChallenge)
            {
                ActualView.SetupScoreChallengeRewardListView(
                    viewModel,
                    OnRewardIconTapped,
                    tabType);
            }
            else
            {
                ActualView.SetupRaidRewardListView(
                    viewModel,
                    OnRewardIconTapped,
                    tabType);
            }
        }
        
        public void SetupRemainingTime(RemainingTimeSpan remainingTimeSpan)
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
        void OnRankTabButtonTapped()
        {
            ViewDelegate.OnRankTabButtonTapped();
        }
        
        [UIAction]
        void OnRaidTabButtonTapped()
        {
            ViewDelegate.OnRaidTabButtonTapped();
        }
    }
}