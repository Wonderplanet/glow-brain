using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.BoxGachaLineupDialog.Presentation.View
{
    public interface IBoxGachaLineupDialogViewDelegate
    {
        void OnViewDidLoad();
        void OnPrizeIconTapped(PlayerResourceIconViewModel playerResourceIconViewModel);
        void OnCloseButtonTapped();
    }
}