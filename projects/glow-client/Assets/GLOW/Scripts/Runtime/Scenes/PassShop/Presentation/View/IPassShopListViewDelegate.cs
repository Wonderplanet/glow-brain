using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.PassShop.Presentation.View
{
    public interface IPassShopListViewDelegate
    {
        void OnViewWillAppear();
        void OnInfoButtonSelected(MasterDataId mstShopPassId);
        void OnPurchaseButtonSelected(
            MasterDataId mstShopPassId,
            Action<RemainingTimeSpan> setUpCellRemainingTimeAction);
        void OnItemIconSelected(PlayerResourceIconViewModel viewModel);
    }
}