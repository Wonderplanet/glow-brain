using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.BattleResult.Presentation.Views.FinishResult
{
    public interface IFinishResultViewDelegate
    {
        void OnViewDidLoad();
        void OnUnloadView();
        void OnSkipSelected();
        void OnCloseSelected();
        void OnRetrySelected();
        void OnBackButton();
        void OnIconSelected(PlayerResourceIconViewModel viewModel);
    }
}
