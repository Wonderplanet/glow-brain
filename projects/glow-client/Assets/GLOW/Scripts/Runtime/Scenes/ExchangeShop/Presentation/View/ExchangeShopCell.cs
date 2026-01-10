using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ExchangeShop.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.ExchangeShop.Presentation.View
{
    public class ExchangeShopCell : UICollectionViewCell
    {
        [Header("タイトル")]
        [SerializeField] UIText _productNameText;

        [Header("商品アイコン")]
        [SerializeField] PlayerResourceIconComponent _productItemIcon;

        [Header("商品情報")]
        [SerializeField] UIText _limitTimeText;
        [SerializeField] UIText _purchasableCountText;
        [SerializeField] GameObject _purchasableObject;

        [Header("消費アイテム情報")]
        [SerializeField] UIImage _costItemIcon;
        [SerializeField] UIText _purchaseCostText;

        [Header("ボタン")]
        [SerializeField] Button _itemDetailButton;
        [SerializeField] Button _purchaseButton;

        public const string ItemDetailIdentifier = "itemDetail";
        public const string PurchaseIdentifier = "purchase";

        void Awake()
        {
            AddButton(_itemDetailButton, ItemDetailIdentifier);
            AddButton(_purchaseButton, PurchaseIdentifier);
        }

        public void Setup(ExchangeShopCellViewModel viewModel)
        {
            SetUpProductName(
                viewModel.ProductName,
                viewModel.ProductResourceType,
                viewModel.ProductResourceAmount);
            SetUpLimitTime(viewModel.LimitTime);
            SetUpPurchasableCount(viewModel.PurchasableCount);
            SetUpItemIcon(viewModel.PlayerResourceIconViewModel);
            SetUpPurchasableButton(viewModel);
        }

        void SetUpProductName(
            ProductName productName,
            ResourceType resourceType,
            ProductResourceAmount amount)
        {
            switch (resourceType)
            {

                case ResourceType.Item:
                case ResourceType.Unit:
                case ResourceType.Emblem:
                case ResourceType.Artwork:
                    _productNameText.SetText(ProductName.WithProductResourceAmount(productName, amount).Value);
                    break;
                default:
                    _productNameText.SetText(ProductName.FromResourceTypeWithProductResourceAmount(resourceType, amount).Value);
                    break;
            }
        }

        void SetUpItemIcon(PlayerResourceIconViewModel viewModel)
        {
            _productItemIcon.Setup(viewModel);
        }

        void SetUpPurchasableButton(ExchangeShopCellViewModel viewModel)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_costItemIcon.Image, viewModel.CostItemIconAssetPath.Value);
            _purchaseCostText.SetText(viewModel.CostItemAmount.ToStringWithMultiplicationSeparated());
        }

        void SetUpLimitTime(RemainingTimeSpan limitTime)
        {
            if (limitTime.IsEmpty())
            {
                _limitTimeText.Hidden = true;
                return;
            }

            _limitTimeText.Hidden = false;
            _limitTimeText.SetText(TimeSpanFormatter.FormatRemaining(limitTime));
        }

        void SetUpPurchasableCount(PurchasableCount purchasableCount)
        {
            if (purchasableCount.IsZero())
            {
                _purchaseButton.gameObject.SetActive(false);
                _purchasableObject.gameObject.SetActive(false);
                return;
            }

            if (purchasableCount.IsInfinity())
            {
                _purchasableCountText.Hidden = true;
                _purchasableObject.gameObject.SetActive(false);
                return;
            }

            _purchaseButton.gameObject.SetActive(true);
            _purchasableObject.gameObject.SetActive(true);

            _purchasableCountText.Hidden = false;
            _purchasableCountText.SetText("あと<color=red>{0}回</color>交換可能", purchasableCount.ToString());
        }
    }
}
