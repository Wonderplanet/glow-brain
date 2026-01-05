using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ShopProductInfo.Presentation.View
{
    public class ShopProductInfoPlateComponent : UIView
    {
        [SerializeField] PlayerResourceIconButtonComponent _playerResourceIconButtonComponent;

        [SerializeField] UIText _productNameText;

        [SerializeField] UIText _productCountText;

        public void Setup(PlayerResourceIconViewModel model, ProductName productName)
        {
            _playerResourceIconButtonComponent.Setup(model);

            _productNameText.SetText(productName.Value);

            _productCountText.SetText(model.Amount.ToStringWithMultiplicationAndSeparate());
        }
    }
}
