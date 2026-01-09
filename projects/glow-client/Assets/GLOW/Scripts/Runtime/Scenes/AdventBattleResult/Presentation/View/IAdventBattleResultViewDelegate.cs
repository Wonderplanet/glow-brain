using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.AdventBattleResult.Presentation.View
{
    public interface IAdventBattleResultViewDelegate
    {
        void OnViewDidAppear();
        void OnUnloadView();
        void OnCloseButtonTapped();
        void OnActionButtonTapped();
        void OnRetryButtonTapped();
        void OnIconSelected(PlayerResourceIconViewModel viewModel);
    }
}
