using System;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ItemBox.Presentation.Extensions;
using GLOW.Scenes.ItemBox.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.ItemBox.Presentation.Views
{
    public class FragmentBoxTradeView : UIView
    {
        static readonly int Disappear = Animator.StringToHash("disappear");
        static readonly int Appear = Animator.StringToHash("appear");

        [Header(("モーダルRoot"))]
        [SerializeField] RectTransform _rootRect;
        [Header(("各種要素"))]
        [SerializeField] UIText _confirmTradeText;
        [SerializeField] ItemIconComponent _itemIconComponent;
        [SerializeField] UIText _itemNameText;
        [SerializeField] AmountSelectionComponent _amountSelectionComponent;
        [SerializeField] Animator _animator;
        [SerializeField] Button _receivedItemIconButton;

        [Header(("使用するアイテムの画像"))]
        [SerializeField] UIImage _offerFragmentImage;

        [Header(("交換前後での所持アイテム変動表示"))]
        [SerializeField] UIText _offerFragmentAmountTextBeforeTrade;
        [SerializeField] UIText _offerFragmentAmountTextAfterTrade;

        [Header(("交換上限数表示"))]
        [SerializeField] UIText _limitReceivableCountText;
        [SerializeField] UIText _unlimitedReceivableCountText;

        [Header(("交換期限表示"))]
        [SerializeField] UIText _remainingTimeText;
        [SerializeField] UIText _unlimitedTimeText;

        [SerializeField] UIImage _tradeButtonGrayout;

        public RectTransform RootRect => _rootRect;
        public ItemAmount SelectedItemAmount => _amountSelectionComponent.Amount;

        Button.ButtonClickedEvent OnReceivedItemIconClicked => _receivedItemIconButton.onClick;

        const string _textColorRedFormat = "<color=#FF0000>{0}</color>";
        const string _textColorBlackFormat = "<color=#000000>{0}</color>";

        public void SetUpAmountSelectionComponent(FragmentBoxTradeViewModel tradeViewModel, Action onAmountChangedAction)
        {
            // 交換可能数と受け取り可能数の最小値が個数選択可能最大値とする
            ItemAmount maxAmount;
            if (tradeViewModel.TradableReceivedAmount.IsEmpty())
            {
                // 交換可能数が無制限の場合は受け取り可能数を最大値とする
                maxAmount = tradeViewModel.RemainingReceivableAmount;
            }
            else
            {
                // 交換可能数が有限の場合は交換可能数と受け取り可能数の最小値を取得
                maxAmount = ItemAmount.Min(
                    tradeViewModel.TradableReceivedAmount,
                    tradeViewModel.RemainingReceivableAmount);
            }

            _amountSelectionComponent.Setup(ItemAmount.One,
                ItemAmount.Max(ItemAmount.One, maxAmount),
                onAmountChangedAction);
        }

        public void SetupTradeConfirmationText(
            ItemName offerFragmentItemName,
            ItemAmount offerFragmentAmount,
            ItemName receivedItemName)
        {
            _confirmTradeText.SetText(ZString.Format("「{0}」を{1}個使用して「{2}」と交換しますか？",
                offerFragmentItemName.Value,
                offerFragmentAmount.ToString(),
                receivedItemName.Value));
        }

        public void SetupReceivedItem(ItemDetailViewModel receivedItemIconDetailViewModel, ItemAmount receivedAmount)
        {
            _itemIconComponent.Setup(receivedItemIconDetailViewModel.ItemIconAssetPath,
                receivedItemIconDetailViewModel.Rarity,
                receivedAmount);

            _itemNameText.SetText(receivedItemIconDetailViewModel.Name.Value);
        }

        public void SetUpOfferFragmentImage(ItemIconAssetPath iconAssetPath)
        {
            UISpriteUtil.LoadSpriteWithFade(_offerFragmentImage.Image, iconAssetPath.Value);
        }

        public void SetupAmountTextBeforeAndAfterTrade(ItemAmount beforeAmount, ItemAmount afterAmount)
        {
            var format = _textColorBlackFormat;
            if (afterAmount.IsMinus()) format = _textColorRedFormat;

            _offerFragmentAmountTextBeforeTrade.SetText(beforeAmount.ToString());
            _offerFragmentAmountTextAfterTrade.SetText(format, afterAmount.ToString());
        }

        public void SetupTradeButtonGrayout(bool isGrayout)
        {
            _tradeButtonGrayout.Hidden = !isGrayout;
        }

        public void SetupRemainingReceivableAmount(ItemAmount remainingReceivableAmount, ItemTradeResetType resetType)
        {
            var isUnlimited = remainingReceivableAmount.IsInfinity();
            _unlimitedReceivableCountText.Hidden = !isUnlimited;
            _limitReceivableCountText.Hidden = isUnlimited;

            if (isUnlimited) return;

            if (remainingReceivableAmount.IsZero())
            {
                _limitReceivableCountText.SetText("<color=#ee3632>交換上限数に到達しました</color>");
            }
            else
            {
                _limitReceivableCountText.SetText(ZString.Format("あと{0}回{1}",
                    remainingReceivableAmount.ToString(),
                    resetType.ToDisplayString()));
            }
        }

        public void SetUpRemainingTimeSpan(RemainingTimeSpan remainingTimeSpan)
        {
            var isLimitedTime = remainingTimeSpan.HasValue();
            _unlimitedTimeText.Hidden = isLimitedTime;
            _remainingTimeText.Hidden = !isLimitedTime;
            if (isLimitedTime)
            {
                _remainingTimeText.SetText(TimeSpanFormatter.FormatUntilEnd(remainingTimeSpan));
            }
        }

        public void SetUpReceivedItemIconButton(Action onReceivedItemIconButtonTapped)
        {
            OnReceivedItemIconClicked.RemoveAllListeners();
            OnReceivedItemIconClicked.AddListener(() =>
            {
                onReceivedItemIconButtonTapped?.Invoke();
            });
        }

        public void PlayShowAnimation()
        {
            _animator.SetTrigger(Appear);
        }

        public void PlayCloseAnimation()
        {
            _animator.SetTrigger(Disappear);
        }
    }
}
