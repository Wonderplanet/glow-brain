using GLOW.Scenes.ItemDetail.Presentation.Views;

namespace GLOW.Scenes.SelectFragmentItemBoxTransit.Presentation
{
    public interface ISelectFragmentItemBoxTransitViewDelegate
    {
        void OnClose();
        void OnTransitionButtonTapped(ItemDetailEarnLocationViewModel earnLocationViewModel);
    }
}
