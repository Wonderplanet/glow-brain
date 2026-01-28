using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.Shop.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.Shop.Presentation.View
{
    public class ShopProductPurchaseCommonView : UIView
    {
        [SerializeField] UIText _productNameText;

        [SerializeField] Button _button;
        public Button.ButtonClickedEvent OnButtonTapped  => _button.onClick;

        protected void SetupCommonUi(ShopProductCellViewModel viewModel)
        {
            _button.gameObject.SetActive(!viewModel.PurchasableCount.IsZero());
        }

        protected void SetupProductName(ProductName productName)
        {
            _productNameText.SetText(productName.Value);
        }
    }
}
