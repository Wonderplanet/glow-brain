using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.AdventBattleRewardList.Presentation.View
{
    public interface IAdventBattleRewardListViewDelegate
    {
        void OnViewDidLoad();
        void OnBackButtonTapped();
        void OnRankingTabButtonTapped();
        void OnRankTabButtonTapped();
        void OnRaidTabButtonTapped();
        void OnItemIconTapped(PlayerResourceIconViewModel viewModel);
    }
}