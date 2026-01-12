using System.Collections;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.UnitLevelUpDialogView.Domain.ValueObjects;
using GLOW.Scenes.UnitLevelUpDialogView.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.UnitLevelUpDialogView.Presentation.Views
{
    public class UnitLevelUpDialogView : UIView
    {
        [Header("レベル")]
        [SerializeField] UIText _currentLevel;
        [SerializeField] UIText _selectLevel;
        [Header("消費")]
        [SerializeField] PlayerResourceIconButtonComponent _consumeCoinIcon;
        [SerializeField] UnitEnhanceItemPossessionComponent _itemPossession;
        [SerializeField] ChildScaler _resourceChildScaler;
        [Header("ステータス")]
        [SerializeField] UnitEnhanceBaseStatusPreviewComponent _baseStatus;
        [SerializeField] UnitEnhanceSpecialAttackPreviewComponent _specialAttackPreview;
        [Header("ボタン")]
        [SerializeField] Button _incrementButton;
        [SerializeField] Button _maximumButton;
        [SerializeField] Button _decrementButton;
        [SerializeField] Button _minimumButton;
        [SerializeField] Button _acceptButton;

        HP _currentHp;
        AttackPower _currentAttackPower;

        public void Setup(
            PlayerResourceIconViewModel iconViewModel,
            UnitLevel currentLevel,
            HP currentHp,
            AttackPower currentAttackPower,
            CharacterUnitRoleType roleType)
        {
            _consumeCoinIcon.Setup(iconViewModel);
            _currentLevel.SetText(currentLevel.ToString());
            _currentHp = currentHp;
            _currentAttackPower = currentAttackPower;

            _baseStatus.Hidden = roleType == CharacterUnitRoleType.Special;
            _specialAttackPreview.Hidden = roleType != CharacterUnitRoleType.Special;
        }

        public void SetSelectLevel(UnitLevelUpValueViewModel viewModel, Coin possessionCoin)
        {
            SetStatusPreview(viewModel.AfterHP, viewModel.AfterAttackPower, viewModel.SpecialAttackName, viewModel.SpecialAttackDescription);
            _selectLevel.SetText(viewModel.Level.ToStringWithPrefixLv());
            _consumeCoinIcon.SetAmount(new PlayerResourceAmount(viewModel.ConsumeCoinValue.HasAmount));
            _acceptButton.interactable = viewModel.ButtonState.EnableAccept;
            SetPossessionCoin(possessionCoin, viewModel.ConsumeCoinValue);
            SetButtonEnable(viewModel.ButtonState);
        }

        public void PlayResourceAppearanceAnimation()
        {
            _resourceChildScaler.Play();
        }

        void SetStatusPreview(HP afterHp, AttackPower afterAttackPower, SpecialAttackName specialAttackName, SpecialAttackInfoDescription specialAttackDescription)
        {
            _baseStatus.SetupHP(_currentHp, afterHp);
            _baseStatus.SetupAttackPower(_currentAttackPower, afterAttackPower);
            _specialAttackPreview.Setup(specialAttackName, specialAttackDescription);
        }

        void SetPossessionCoin(Coin possessionCoin, Coin consumeCoin)
        {
            _itemPossession.SetupCoin(possessionCoin, consumeCoin);
        }

        void SetButtonEnable(LevelUpButtonState buttonState)
        {
            // SE再生の都合上１フレーム遅らせてボタンの有効無効を切り替える
            StartCoroutine(SetButtonEnableCoroutine(buttonState));
        }

        IEnumerator SetButtonEnableCoroutine(LevelUpButtonState buttonState)
        {
            yield return null;
            _minimumButton.interactable = buttonState.EnableMinimum;
            _decrementButton.interactable = buttonState.EnableMinus;
            _incrementButton.interactable = buttonState.EnablePlus;
            _maximumButton.interactable = buttonState.EnableMaximum;
        }
    }
}
