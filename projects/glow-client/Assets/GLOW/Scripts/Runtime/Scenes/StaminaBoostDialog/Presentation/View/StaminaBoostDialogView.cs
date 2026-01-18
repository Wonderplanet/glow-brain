using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.StaminaBoostDialog.Presentation.View
{
    public class StaminaBoostDialogView : UIView
    {
        [SerializeField] UIText _consumeStaminaText;
        [SerializeField] UIText _currentStaminaText;
        [SerializeField] UIText _afterStaminaText;
        [SerializeField] UIImage _consumeStaminaIcon;
        [SerializeField] UIImage _currentStaminaIcon;
        [SerializeField] AmountSelectionComponent _amountSelectionComponent;
        [SerializeField] UITextButton _startButton;
        [SerializeField] UITextButton _cancelButton;

        public ItemAmount SelectedItemAmount => _amountSelectionComponent.Amount;

        public void SetAmountSelection(
            StaminaBoostCount count,
            StaminaBoostCount limitCount,
            Action onAmountChanged)
        {
            _amountSelectionComponent.Setup(
                new ItemAmount(count.Value),
                new ItemAmount(limitCount.Value),
                onAmountChanged);
        }

        public void SetConsumeStaminaText(StageConsumeStamina stamina)
        {
            _consumeStaminaText.SetText(stamina.ToString());
        }

        public void SetCurrentStaminaText(Stamina stamina)
        {
            _currentStaminaText.SetText(stamina.ToString());
        }

        public void SetAfterStaminaText(Stamina stamina)
        {
            _afterStaminaText.SetText(stamina.ToString());
            _afterStaminaText.SetColor(stamina.Value >= 0 ? Color.black : Color.white);
        }

        public void SetStaminaIcon(StaminaIconAssetPath staminaIconAssetPath)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_consumeStaminaIcon.Image, staminaIconAssetPath.Value);
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_currentStaminaIcon.Image, staminaIconAssetPath.Value);
        }

        public void SetButtonInteractable(bool isInteractable)
        {
            _startButton.interactable = isInteractable;
            _cancelButton.interactable = isInteractable;
        }
    }
}
