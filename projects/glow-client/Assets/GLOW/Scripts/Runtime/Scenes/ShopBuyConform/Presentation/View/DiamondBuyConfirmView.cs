using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ShopBuyConform.Presentation.Component;
using GLOW.Scenes.ShopBuyConform.Presentation.ViewModel;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ShopBuyConform.Presentation.View
{
    public class DiamondBuyConfirmView : UIView
    {
        [SerializeField] UIText _messageText;

        [SerializeField] UseResourceAmountChangeDisplayComponent _paidUseResourceAmountChangeDisplayComponent;

        [SerializeField] UseResourceAmountChangeDisplayComponent _freeUseResourceAmountChangeDisplayComponent;
        
        [SerializeField] UIText _attentionNotEnoughDiamondText;

        [SerializeField] ShopProductPlateComponent _productPlateComponent;

        public void Setup(ProductBuyWithDiamondConfirmationViewModel viewModel, bool isEnough)
        {
            _messageText.SetText(GetMessage(viewModel.CostAmount, viewModel.ProductName));
            _paidUseResourceAmountChangeDisplayComponent.SetupPaidDiamondAmount(
                viewModel.CurrentPaidDiamond,
                viewModel.AfterPaidDiamond);
            _freeUseResourceAmountChangeDisplayComponent.SetupFreeDiamondAmount(
                viewModel.CurrentFreeDiamond,
                viewModel.AfterFreeDiamond);

            _productPlateComponent.IsVisible = !viewModel.PlayerResourceIconViewModel.IsEmpty();
            if(_productPlateComponent.IsVisible)
            {
                _productPlateComponent.Setup(
                    viewModel.PlayerResourceIconViewModel,
                    viewModel.ProductName,
                    viewModel.PlayerResourceIconViewModel.Amount,
                    DiscountRate.Empty);
            }

            _attentionNotEnoughDiamondText.Hidden = isEnough;
        }

        string GetMessage(CostAmount costAmount, ProductName productName)
        {
            return ZString.Format("プリズムを{0}個使用して\n「{1}」\nを購入しますか？", costAmount.ToString(), productName.Value);
        }
    }
}
