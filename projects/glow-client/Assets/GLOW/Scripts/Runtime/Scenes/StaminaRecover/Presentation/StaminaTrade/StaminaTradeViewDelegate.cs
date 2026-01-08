using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.StaminaRecover.Presentation.StaminaTrade
{
    public interface IStaminaTradeViewDelegate
    {
        void OnViewDidLoad();
        void OnBuyStaminaButtonTapped(MasterDataId mstItemId, ItemAmount amount);
        void OnItemIconTapped(PlayerResourceIconViewModel itemIconViewModel);
        void OnCloseButtonTapped();
    }
}
