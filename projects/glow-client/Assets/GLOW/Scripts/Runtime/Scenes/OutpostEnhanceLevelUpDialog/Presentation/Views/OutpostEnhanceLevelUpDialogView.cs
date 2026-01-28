using System.Collections;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.OutpostEnhanceLevelUpDialog.Domain.ValueObjects;
using GLOW.Scenes.OutpostEnhanceLevelUpDialog.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.OutpostEnhanceLevelUpDialog.Presentation.Views
{
    public class OutpostEnhanceLevelUpDialogView : UIView
    {
        [SerializeField] UIText _currentLevel;
        [SerializeField] UIText _selectLevel;
        [SerializeField] UIText _requireCoin;
        [SerializeField] UIText _possessionCoin;
        [SerializeField] UIText _consumedCoin;
        [SerializeField] Button _incrementButton;
        [SerializeField] Button _maximumButton;
        [SerializeField] Button _decrementButton;
        [SerializeField] Button _minimumButton;
        [SerializeField] Button _acceptButton;

        [Header("消費後コインのフォントマテリアル")]
        [SerializeField] Material _consumedPlusFontMaterial;
        [SerializeField] Material _consumedMinusFontMaterial;

        public void Setup(OutpostEnhanceLevel currentLevel, Coin possessionCoin)
        {
            _currentLevel.SetText(currentLevel.Value.ToString());
            _possessionCoin.SetText(AmountFormatter.FormatAmount(possessionCoin.Value));
        }

        public void SetSelectLevel(OutpostEnhanceLevelUpValueViewModel viewModel)
        {
            _selectLevel.SetText(viewModel.Level.ToStringWithPrefixLv());
            _requireCoin.SetText(AmountFormatter.FormatAmount(viewModel.RequiredCoin.Value));
            _consumedCoin.SetText(AmountFormatter.FormatAmount(viewModel.ConsumedCoin.Value));

            var consumedCoinMaterial = viewModel.ConsumedCoin >= Coin.Zero
                ? _consumedPlusFontMaterial
                : _consumedMinusFontMaterial;
            _consumedCoin.SetMaterial(consumedCoinMaterial);
            
            _acceptButton.interactable = viewModel.ButtonState.EnableAccept;
            SetButtonEnable(viewModel.ButtonState);
        }

        void SetButtonEnable(OutpostEnhanceLevelUpButtonState buttonState)
        {
            // SE再生の都合上１フレーム遅らせてボタンの有効無効を切り替える
            StartCoroutine(SetButtonEnableCoroutine(buttonState));
        }

        IEnumerator SetButtonEnableCoroutine(OutpostEnhanceLevelUpButtonState buttonState)
        {
            yield return null;
            _minimumButton.interactable = buttonState.EnableMinimum;
            _decrementButton.interactable = buttonState.EnableMinus;
            _incrementButton.interactable = buttonState.EnablePlus;
            _maximumButton.interactable = buttonState.EnableMaximum;
        }
    }
}
