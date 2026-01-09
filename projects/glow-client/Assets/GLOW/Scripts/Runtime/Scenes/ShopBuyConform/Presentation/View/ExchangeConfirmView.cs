using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ShopBuyConform.Presentation.Component;
using GLOW.Scenes.ShopBuyConform.Presentation.ViewModel;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ShopBuyConform.Presentation.View
{
    public class ExchangeConfirmView : UIView
    {
        [SerializeField] UIText _messageText;
        [SerializeField] AmountChangeDisplayComponent _amountChangeDisplayComponent;

        public void Setup(ExchangeConfirmViewModel viewModel)
        {
            _messageText.SetText("{0}\nを{1}使用して\n「{2}」\nを交換しますか？",
                viewModel.ConsumptionItemName.Value,
                viewModel.ConsumptionAmount.ToStringSeparated(),
                viewModel.AcquisitionItemName.Value);

            _amountChangeDisplayComponent.Setup(
                viewModel.ConsumptionItemIconAssetPath,
                viewModel.ConsumptionItemBeforeAmount,
                viewModel.ConsumptionItemAfterAmount);
        }
    }
}
