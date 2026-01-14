using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.BoxGachaResult.Presentation.View
{
    public interface IBoxGachaResultViewDelegate
    {
        void OnViewDidLoad();
        void OnIconCellTapped(PlayerResourceIconViewModel viewModel);
        void OnCloseButtonTapped();
    }
}