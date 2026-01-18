using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ShopBuyConform.Presentation.Component;
using GLOW.Scenes.ShopBuyConform.Presentation.ViewModel;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ShopBuyConform.Presentation.View
{
    public class CashBuyConfirmView : UIView
    {
        [SerializeField] ShopProductPlateComponent _productPlateComponent;

        [SerializeField] ShopProductNamePlateComponent _namePlateComponent;

        [SerializeField] UIText _priceText;

        public void Setup(ProductBuyWithCashConfirmationViewModel viewModel)
        {
            if (viewModel.ProductType == ProductType.Diamond)
            {
                SetupDiamondPlate(viewModel);
            }
            else
            {
                SetupPackPlate(viewModel);
            }

            if (viewModel.DisplayCostType == DisplayCostType.Free)
            {
                _priceText.SetText("無料");
            }
            else
            {
                _priceText.SetText(viewModel.ProductPrice.ToString());
            }
        }

        void SetupDiamondPlate(ProductBuyWithCashConfirmationViewModel viewModel)
        {
            _productPlateComponent.Hidden = false;
            _productPlateComponent.Setup(viewModel.PlayerResourceIconViewModel, viewModel.ProductName,
                viewModel.PlayerResourceIconViewModel.Amount, viewModel.DiscountRate);

        }

        void SetupPackPlate(ProductBuyWithCashConfirmationViewModel viewModel)
        {
            _namePlateComponent.Hidden = false;
            _namePlateComponent.Setup(viewModel.ProductName, viewModel.DiscountRate);
        }
    }
}
