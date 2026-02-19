using System;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Shop.Domain.Extension;
using GLOW.Scenes.Shop.Presentation.View;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.Shop.Presentation.Component
{
    public class ShopSectionHeaderView : UICollectionViewSectionHeader
    {
        [SerializeField] ShopCategoryNameTextComponent _categoryNameTextComponent;

        [SerializeField] ShopCategoryTimeComponent _timeComponent;
        [SerializeField] Button _purchaseHistoryButton;

        public void SetupShopSection(
            DisplayShopProductType type,
            RemainingTimeSpan updateTime,
            Action onPurchaseHistoryButtonTapped = null)
        {
            var shouldShowPurchaseHistoryButton = onPurchaseHistoryButtonTapped != null;
            _purchaseHistoryButton.gameObject.SetActive(shouldShowPurchaseHistoryButton);
            _categoryNameTextComponent.Setup(type);
            UpdateTime(type, updateTime);
            SetUpPurchaseHistoryButton(onPurchaseHistoryButtonTapped);
        }

        void UpdateTime(DisplayShopProductType type, RemainingTimeSpan updateTime)
        {
            var visible = type.HasNextUpdateTime();
            _timeComponent.IsVisible = visible;

            if (!visible) return;

            _timeComponent.Setup(updateTime);
        }

        void SetUpPurchaseHistoryButton(Action onTapped)
        {
            if (onTapped == null) return;

            _purchaseHistoryButton.onClick.RemoveAllListeners();
            _purchaseHistoryButton.onClick.AddListener(() => onTapped());
        }
    }
}
