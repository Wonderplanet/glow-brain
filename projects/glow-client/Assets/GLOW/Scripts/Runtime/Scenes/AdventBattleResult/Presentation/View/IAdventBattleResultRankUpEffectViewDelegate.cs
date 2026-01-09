using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.AdventBattleResult.Presentation.View
{
    public interface IAdventBattleResultRankUpEffectViewDelegate
    {
        void OnViewDidAppear();
        void OnUnloadView();
        void OnCloseButtonTapped();
        void OnSkipButtonTapped();
        void OnPlayerResourceIconTapped(PlayerResourceIconViewModel viewModel);
    }
}
