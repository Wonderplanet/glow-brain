
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ItemDetail.Presentation.Views;

namespace GLOW.Scenes.FragmentProvisionRatio.Presentation
{
    public interface IFragmentProvisionRatioViewDelegate
    {
        void OnViewDidLoad();
        void OnShowUnitView(MasterDataId mstUnitId);
        void OnTransitShop();
        void OnClose();
        void OnTransitionButtonTapped(ItemDetailEarnLocationViewModel earnLocationViewModel);
    }
}
