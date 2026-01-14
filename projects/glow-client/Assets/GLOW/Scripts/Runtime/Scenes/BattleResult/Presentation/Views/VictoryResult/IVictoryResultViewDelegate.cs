using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.BattleResult.Presentation.Views
{
    public interface IVictoryResultViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidUnload();
        void OnSkipSelected();
        void OnCloseSelected();
        void OnRetrySelected();
        void OnBackButton();
        void OnIconSelected(PlayerResourceIconViewModel viewModel);
    }
}
