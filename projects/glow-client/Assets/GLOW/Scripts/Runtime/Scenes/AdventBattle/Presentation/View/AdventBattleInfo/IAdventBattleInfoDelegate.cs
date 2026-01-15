using GLOW.Core.Presentation.ViewModels;
namespace GLOW.Scenes.AdventBattle.Presentation.View.AdventBattleInfo
{
    public interface IAdventBattleInfoDelegate
    {
        void OnViewWillAppear();
        void OnCloseButtonTapped();
        void OnTappedPlayerResourceIcon(PlayerResourceIconViewModel viewModel);
    }
}