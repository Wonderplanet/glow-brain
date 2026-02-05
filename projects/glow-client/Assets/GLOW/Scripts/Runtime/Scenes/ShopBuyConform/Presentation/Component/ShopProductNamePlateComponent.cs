using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.PackShop.Presentation.Views;
using UnityEngine;

namespace GLOW.Scenes.ShopBuyConform.Presentation.Component
{
    public class ShopProductNamePlateComponent : UIObject
    {
        [SerializeField] UIText _productNameText;

        [SerializeField] ShopDiscountRateComponent _discountRateComponent;

        public void Setup(ProductName productName, DiscountRate rate)
        {
            _productNameText.SetText(productName.Value);
            _discountRateComponent.SetDiscountRate(rate);
            _discountRateComponent.gameObject.SetActive(!rate.IsZero());
        }
    }
}
