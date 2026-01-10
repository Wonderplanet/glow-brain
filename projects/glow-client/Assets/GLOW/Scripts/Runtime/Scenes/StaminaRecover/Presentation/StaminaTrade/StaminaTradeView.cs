using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.StaminaRecover.Presentation.ViewModel;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.StaminaRecover.Presentation.StaminaTrade
{
    public class StaminaTradeView : UIView
    {
        [Header("アイコン")]
        [SerializeField] ItemIconComponent _itemIcon;

        [Header("テキスト")]
        [SerializeField] UIText _itemText;
        [SerializeField] UIText _confirmText;

        [Header("スタミナ")]
        [SerializeField] UIText _staminaBeforeText;
        [SerializeField] UIText _staminaAfterText;

        [Header("数量選択コンポーネント")]
        [SerializeField] AmountSelectionComponent _amountSelectionComponent;

        public ItemAmount SelectedAmount => _amountSelectionComponent.Amount;

        const string _confirmTextFormat = "{0}を{1}個使用して\nスタミナを{2}回復しますか？";
        Action _updateAction;

        public void Setup(StaminaTradeViewModel viewModel, Action updateAction)
        {
            _updateAction = updateAction;

            SetUpAmountSelection(viewModel.MaxPurchasableCount);
            SetUpItemIcon(viewModel.ItemIconViewModel);
            SetConfirmText(viewModel.Name, _amountSelectionComponent.Amount, viewModel.EffectValue);
            SetItemNameText(viewModel.Name);
            SetStaminaTexts(viewModel.CurrentUserStamina, viewModel.EffectValue, viewModel.MaxStamina);
        }

        public void SetStaminaTexts(Stamina currentStamina, Stamina effectValue, Stamina maxStamina)
        {
            _staminaBeforeText.SetText("{0}", currentStamina.Value);

            var afterStamina =
                new Stamina(currentStamina.Value + (effectValue.Value * _amountSelectionComponent.Amount.Value));

            if(afterStamina > maxStamina) afterStamina = maxStamina;

            _staminaAfterText.SetText("{0}", afterStamina.Value);
        }

        public void SetConfirmText(ItemName itemName, ItemAmount selectedAmount, Stamina effectValue)
        {
            _confirmText.SetText(_confirmTextFormat, itemName.Value, selectedAmount.Value, effectValue.Value);
        }

        void SetItemNameText(ItemName itemName)
        {
            _itemText.SetText(itemName.Value);
        }

        void SetUpItemIcon(PlayerResourceIconViewModel itemIconViewModel)
        {
            _itemIcon.Setup(
                new ItemIconAssetPath(itemIconViewModel.AssetPath.Value),
                itemIconViewModel.Rarity,
                new ItemAmount(itemIconViewModel.Amount.Value));
        }

        void SetUpAmountSelection(PurchasableCount maxPurchasableCount)
        {
            var maxAmount = new ItemAmount(maxPurchasableCount.Value);
            _amountSelectionComponent.Setup(ItemAmount.One, maxAmount, _updateAction);
        }
    }
}
