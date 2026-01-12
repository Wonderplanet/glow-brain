using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.PvpRewardList.Presentation.View
{
    public interface IPvpRewardListViewDelegate
    {
        void OnViewDidLoad();
        void OnItemIconTapped(PlayerResourceIconViewModel viewModel);
        void OnBackButtonTapped();
        void OnRankingTabButtonTapped();
        void OnRankRewardTabButtonTapped();
        void OnTotalScoreTabButtonTapped();
    }
}