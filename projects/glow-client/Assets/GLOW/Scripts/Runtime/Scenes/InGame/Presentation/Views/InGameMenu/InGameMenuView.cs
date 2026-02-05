using System;
using System.Diagnostics.CodeAnalysis;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using GLOW.Modules.GameOption.Domain.Constants;
using GLOW.Modules.GameOption.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Constants;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Views.InGameMenu
{
    public sealed class InGameMenuView : UIView
    {
        [Serializable]
        [SuppressMessage("ReSharper", "InconsistentNaming")]
        public struct SettingButtons
        {
            public GameObject onButton;
            public GameObject offButton;

            public void SetToggleOn(bool isOn)
            {
                onButton.SetActive(isOn);
                offButton.SetActive(!isOn);
            }
        }

        [SerializeField] CanvasGroup _canvasGroup;
        [Header("メニュー画面")]
        [SerializeField] InGameMenuTitleBackComponent _titleBackView;
        [SerializeField] SettingButtons _settingBgmButton;
        [SerializeField] SettingButtons _settingSeButton;
        [SerializeField] SettingButtons _settingTwoRowDeckButton;
        [SerializeField] SettingButtons _settingDamageDisplayButton;
        [SerializeField] UIToggleableComponentGroup _specialAttackCutInToggleableGroup;
        [SerializeField] UIObject backHomeButton;
        [SerializeField] UIObject giveUpButton;

        public CanvasGroup CanvasGroup => _canvasGroup;

        public bool isTitleBackViewHidden
        {
            get => _titleBackView.Hidden;
        }

        public void SetBgmToggleOn(bool isOn)
        {
            _settingBgmButton.SetToggleOn(isOn);
        }

        public void SetSeToggleOn(bool isOn)
        {
            _settingSeButton.SetToggleOn(isOn);
        }

        public void SetSpecialAttackCutInToggleOn(SpecialAttackCutInPlayType specialAttackCutInPlayType)
        {
            _specialAttackCutInToggleableGroup.SetToggleOn(specialAttackCutInPlayType.ToString());
        }

        public void SetTwoRowDeckToggleOn(bool isOn)
        {
            _settingTwoRowDeckButton.SetToggleOn(isOn);
        }

        public void SetDamageDisplayToggleOn(bool isOn)
        {
            _settingDamageDisplayButton.SetToggleOn(isOn);
        }

        public void SetUpTitleBackViewAttention(InGameConsumptionType inGameConsumptionType, InGameTypePvpFlag isPvp)
        {
            _titleBackView.SetUpAttention(inGameConsumptionType, isPvp);
        }

        public void SetTitleBackViewHidden(bool isHidden)
        {
            _titleBackView.Hidden = isHidden;
        }

        public void SetGiveUpButton(CanGiveUpFlag canGiveUp)
        {
            backHomeButton.IsVisible = !canGiveUp;
            giveUpButton.IsVisible = canGiveUp;
        }
    }
}
