using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.ItemBox.Presentation.Views
{
    public interface IFragmentBoxTradeViewDelegate
    {
        void OnViewDidLoad();
        void OnTradeButtonTapped(MasterDataId offerItemId, ItemAmount amount);
        void OnCancelButtonTapped();
        void OnItemIconTapped(MasterDataId id);
    }
}