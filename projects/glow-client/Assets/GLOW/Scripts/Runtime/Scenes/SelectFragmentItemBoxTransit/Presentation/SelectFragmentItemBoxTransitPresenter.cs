using GLOW.Scenes.ItemBox.Presentation.Presenters;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.ItemDetail.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.SelectFragmentItemBoxTransit.Presentation
{
    public class SelectFragmentItemBoxTransitPresenter : ISelectFragmentItemBoxTransitViewDelegate
    {
        [Inject] SelectionFragmentBoxWireFrame SelectionFragmentBoxWireFrame { get; }
        [Inject] ItemDetailTransitionWireFrame ItemDetailTransitionWireFrame { get; }

        void ISelectFragmentItemBoxTransitViewDelegate.OnClose()
        {
            SelectionFragmentBoxWireFrame.OnCloseInfoButton();
        }

        void ISelectFragmentItemBoxTransitViewDelegate.OnTransitionButtonTapped(ItemDetailEarnLocationViewModel earnLocationViewModel)
        {
            ItemDetailTransitionWireFrame.Transit(earnLocationViewModel);
        }
    }
}
