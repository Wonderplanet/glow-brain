using System;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ExchangeShop.Domain.Constants;
using GLOW.Scenes.ExchangeShop.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.ExchangeShop.Presentation.View
{
    public class ExchangeShopConfirmView : UIView
    {
        [Header("テキスト")]
        [SerializeField] UIText _tradeConfirmText;
        [Header("交換アイテム")]
        [SerializeField] ItemIconComponent _tradeItemIcon;
        [SerializeField] UIText _tradeNameText;
        [Header("消費アイテム")]
        [SerializeField] UIImage _costItemIconImage;
        [SerializeField] AmountSelectionComponent _amountSelectionComponent;
        [Header("交換前後所持数表示")]
        [SerializeField] UIText _costItemAmountBeforeTradeText;
        [SerializeField] UIText _costItemAmountAfterTradeText;
        [Header("交換上限数表示")]
        [SerializeField] UIText _limitTradeCountText;
        [Header("交換期限表示")]
        [SerializeField] UIText _remainingTimeText;
        [Header("交換ボタン")]
        [SerializeField] UITextButton _tradeConfirmButton;
        [SerializeField] GameObject _grayOutButtonObject;

        string _confirmFromat = "「{0}」を{1}個使用して「{2}」と交換しますか？";
        const string _limitTradeCountFormat = "あと{0}回";

        const string _textColorRedFormat = "<color=#FF0000>{0}</color>";
        const string _textColorBlackFormat = "<color=#000000>{0}</color>";

        public ItemAmount SelectedTradeAmount => _amountSelectionComponent.Amount;

        public void Setup(ExchangeConfirmViewModel viewModel, Action onChangeSelectAmount)
        {
            SetUpProductNameText(viewModel.ExchangeItemName);
            SetConfirmText(viewModel.CostItemName, viewModel.CostItemAmount, viewModel.ExchangeItemName);
            SetUpTradeItemIcon(viewModel.ExchangeItemIconViewModel);
            SetCostItem(viewModel.CostItemIconAssetPath);
            SetUpAmountSelectionComponent(viewModel, onChangeSelectAmount);
            SetPurchaseAmountText(viewModel.MaxPurchaseCount);
            SetRemainingTimeText(viewModel.LimitTime);
            SetCostAmountText(viewModel.CurrentCostItemAmount,
                viewModel.CurrentCostItemAmount - viewModel.CostItemAmount);
        }

        public void SetConfirmText(ItemName costItemName, ItemAmount costItemCount, ItemName tradeItemName)
        {
            _tradeConfirmText.SetText(_confirmFromat, costItemName.Value, costItemCount.Value, tradeItemName.Value);
        }

        public void SetUpTradeItemIcon(ItemIconViewModel iconViewModel)
        {
            _tradeItemIcon.Setup(
                iconViewModel.ItemIconAssetPath,
                iconViewModel.Rarity,
                iconViewModel.Amount);
        }

        public void SetPurchaseAmountText(PurchaseCount purchaseCount)
        {
            if (purchaseCount.IsInfinity())
            {
                _limitTradeCountText.SetText("無制限");
                return;
            }

            _limitTradeCountText.SetText(_limitTradeCountFormat, purchaseCount.Value.ToString());
        }

        public void SetCostAmountText(
            ItemAmount currentCostItemAmount,
            ItemAmount afterCostItemAmount)
        {
            string format = _textColorBlackFormat;
            if (afterCostItemAmount.IsMinus())
            {
                format = _textColorRedFormat;
            }

            _costItemAmountBeforeTradeText.SetText(currentCostItemAmount.ToString());
            _costItemAmountAfterTradeText.SetText(format, afterCostItemAmount.ToString());
        }

        public void SetTradeButtonGrayOut(bool isActive)
        {
            _grayOutButtonObject.SetActive(isActive);
        }

        void SetUpProductNameText(ItemName productName)
        {
            _tradeNameText.SetText(productName.Value);
        }

        void SetCostItem(ItemIconAssetPath iconAssetPath)
        {
            UISpriteUtil.LoadSpriteWithFade(_costItemIconImage.Image, iconAssetPath.Value);
        }

        void SetRemainingTimeText(RemainingTimeSpan remainingTime)
        {
            if (remainingTime.IsInfinity())
            {
                _remainingTimeText.SetText("無期限");
                return;
            }

            _remainingTimeText.SetText(TimeSpanFormatter.FormatRemaining(remainingTime));
        }

        void SetUpAmountSelectionComponent(ExchangeConfirmViewModel viewModel, Action onChangeSelectAmount)
        {
            var maxAmount = ExchangeTradeConst.MaxExchangeCountPerTime.Value;
            maxAmount = Math.Min(maxAmount, viewModel.CurrentMaxPurchaseCount.Value);
            if (maxAmount <= 0) maxAmount = 1;
            _amountSelectionComponent.Setup(ItemAmount.One, new ItemAmount(maxAmount), onChangeSelectAmount);
        }
    }
}
