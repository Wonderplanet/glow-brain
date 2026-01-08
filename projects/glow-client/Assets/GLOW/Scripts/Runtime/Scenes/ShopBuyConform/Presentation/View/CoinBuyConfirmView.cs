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
    public class CoinBuyConfirmView : UIView
    {
        [SerializeField] UIText _messageText;
        [SerializeField] UseResourceAmountChangeDisplayComponent _coinAmountChangeDisplayComponent;
        [SerializeField] UIText _attentionText;
        [SerializeField] ShopProductPlateComponent _productPlateComponent;

        public void Setup(ProductBuyWithCoinConfirmationViewModel viewModel)
        {
            _messageText.SetText(GetMessage(viewModel.CostAmount, viewModel.ProductName));
            _coinAmountChangeDisplayComponent.SetupCoinAmount(viewModel.CurrentCoin, viewModel.AfterCoin);

            _productPlateComponent.IsVisible = !viewModel.PlayerResourceIconViewModel.IsEmpty();
            if(_productPlateComponent.IsVisible)
            {
                _productPlateComponent.Setup(
                    viewModel.PlayerResourceIconViewModel,
                    viewModel.ProductName,
                    viewModel.PlayerResourceIconViewModel.Amount,
                    DiscountRate.Empty);
            }

            SetAttentionTextVisible(viewModel.AfterCoin.IsMinus());
        }

        string GetMessage(CostAmount costAmount, ProductName productName)
        {
            return ZString.Format("コインを{0}使用して\n「{1}」\nを交換しますか？", costAmount.ToString(), productName.Value);
        }

        void SetAttentionTextVisible(bool visible)
        {
            _attentionText.IsVisible = visible;
        }
    }
}
