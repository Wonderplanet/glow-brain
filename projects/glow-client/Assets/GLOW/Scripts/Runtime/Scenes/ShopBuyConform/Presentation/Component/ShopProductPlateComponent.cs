using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.PackShop.Presentation.Views;
using UnityEngine;

namespace GLOW.Scenes.ShopBuyConform.Presentation.Component
{
    public class ShopProductPlateComponent : UIObject
    {

        [SerializeField] PlayerResourceIconComponent _playerResourceIconComponent;

        [SerializeField] UIText _productNameText;

        [SerializeField] UIText _productAmountText;

        [SerializeField] ShopDiscountRateComponent _discountRateComponent;

        public void Setup(PlayerResourceIconViewModel viewModel, ProductName productName, PlayerResourceAmount amount,
            DiscountRate rate)
        {
            // アイコンの方は個数表記を消す(個数はテキストで表示するため)
            _playerResourceIconComponent.Setup(viewModel);
            _playerResourceIconComponent.SetAmount(PlayerResourceAmount.Empty);

            _productNameText.SetText(productName.Value);
            _productAmountText.SetText(amount.ToStringSeparated());
            _discountRateComponent.SetDiscountRate(rate);
        }
    }
}
