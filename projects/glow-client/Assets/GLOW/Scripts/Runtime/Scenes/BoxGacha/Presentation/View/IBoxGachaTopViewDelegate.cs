using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.BoxGacha.Presentation.View
{
    public interface IBoxGachaTopViewDelegate
    {
        void OnViewDidLoad();
        void OnCloseButtonTapped();
        void OnBoxGachaLineupButtonTapped();
        void OnBoxGachaResetButtonTapped();
        void OnBoxGachaDrawButtonTapped();
        void OnPrizeIconTapped(PlayerResourceIconViewModel prizeIconViewModel);
    }
}