using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using GLOW.Scenes.Shop.Presentation.View;
using GLOW.Scenes.Shop.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.Shop.Presentation.Component
{
    public class ShopCellView : UICollectionViewCell
    {
        [SerializeField] ShopCellCategoryImageComponent _categoryImageComponent;
        [SerializeField] ShopCellProductNameTextComponent _productNameTextComponent;
        [SerializeField] ShopCellPurchasedTextComponent _purchasedTextComponent;
        [SerializeField] ShopCellPurchaseButtonComponent _purchaseButtonComponent;
        [SerializeField] ShopCellResourceComponent _resourceComponent;
        [SerializeField] Button _purchaseButton;
        [SerializeField] Button _infoButton;
        [SerializeField] Button _itemDetailButton;
        [SerializeField] UIImage _newBadgeComponent;
        [SerializeField] ShopCellPurchasableCountPlateComponent _purchasableCountPlateComponent;
        [SerializeField] ShopProductDisplayTermComponent _shopProductDisplayTermComponent;
        [SerializeField] UIImage _purchaseBadgeImage;
        
        public const string InfoButtonKey = "info";
        public const string PurchaseButtonKey = "purchase";
        public const string ItemIconKey = "itemDetail";

        protected override void Awake()
        {
            base.Awake();

            AddButton(_itemDetailButton, ItemIconKey);
            AddButton(_purchaseButton, PurchaseButtonKey);
            AddButton(_infoButton, InfoButtonKey);
        }

        public void Setup(
            ShopProductCellViewModel viewModel,
            HeldAdSkipPassInfoViewModel heldAdSkipPassInfoViewModel,
            bool notUpdateProductComponent = false)
        {
            _productNameTextComponent.Setup(viewModel.ProductName, viewModel.ResourceType, viewModel.ProductResourceAmount);
            _categoryImageComponent.Setup(viewModel.DisplayCostType);
            _purchasedTextComponent.Setup(viewModel.DisplayCostType);
            _purchaseButtonComponent.Setup(
                viewModel.PurchasableCount,
                viewModel.CostAmount,
                viewModel.RawProductPriceText,
                viewModel.DisplayCostType,
                viewModel.IsFirstTimeFreeDisplay,
                heldAdSkipPassInfoViewModel);
            _purchasableCountPlateComponent.Setup(
                viewModel.PurchasableCount,
                viewModel.DisplayCostType,
                viewModel.IsFirstTimeFreeDisplay);
            _shopProductDisplayTermComponent.IsVisible = !viewModel.PurchasableTerm.IsEmpty();
            _shopProductDisplayTermComponent.Setup(viewModel.PurchasableTerm);
            _newBadgeComponent.Hidden = !viewModel.NewFlag.Flg;
            _purchaseBadgeImage.Hidden = viewModel.DisplayCostType != DisplayCostType.Ad || viewModel.PurchasableCount.IsZero();

            if (notUpdateProductComponent) return;

            _resourceComponent.Setup(viewModel);
        }
    }
}
