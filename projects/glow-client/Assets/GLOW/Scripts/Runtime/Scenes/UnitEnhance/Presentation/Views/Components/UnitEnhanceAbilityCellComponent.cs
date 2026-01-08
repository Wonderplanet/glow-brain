using System;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;
using WonderPlanet.ToastNotifier;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.UnitEnhance.Presentation.Views.Components
{
    public class UnitEnhanceAbilityCellComponent : UIObject
    {
        const string EmptyText = "-";

        [SerializeField] UIImage _abilityIcon;
        [SerializeField] UIText _abilityDescription;
        [SerializeField] UIObject _lockedIcon;
        [SerializeField] UIObject _noMessageIcon;
        [SerializeField] Button _lockedButton;

        public void Setup(UnitEnhanceAbilityViewModel viewModel)
        {
            if (!viewModel.IsEmpty() && !viewModel.Ability.IsEmpty())
            {
                SetupAbility(viewModel);
            }
            else
            {
                SetupEmpty();
            }
        }

        void SetupAbility(UnitEnhanceAbilityViewModel viewModel)
        {
            var shouldShowAbilityIcon = viewModel.Ability.Type != UnitAbilityType.None;

            _abilityIcon.Hidden = !shouldShowAbilityIcon;
            if (shouldShowAbilityIcon)
            {
                var iconPath = viewModel.Ability.AbilityIconPath;
                UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                    _abilityIcon.Image,
                    iconPath.Value,
                    () =>
                    {
                        if (!_abilityIcon) return;
                        _abilityIcon.enabled = true;
                    });
            }

            _abilityDescription.SetText(viewModel.Ability.Description);

            if (_noMessageIcon != null)
            {
                // ロック状態でロックアイコンがない時は「-」を表示する形となる
                _noMessageIcon.Hidden = !(viewModel.IsLock && _lockedIcon == null);
            }

            if (_lockedIcon != null)
            {
                _lockedIcon.Hidden = !viewModel.IsLock;
            }

            if (_lockedButton != null)
            {
                _lockedButton.interactable = viewModel.IsLock;
                _lockedButton.onClick.RemoveAllListeners();
                _lockedButton.onClick.AddListener(() =>
                {
                    CommonToastWireFrame.ShowScreenCenterToast(
                        ZString.Format("Lv.強化上限を{0}まで開放で使用可能",
                        viewModel.UnlockUnitLevel.ToStringWithPrefixLv()));
                });
            }
        }

        void SetupEmpty()
        {
            _abilityDescription.SetText(EmptyText);
            _abilityIcon.Hidden = true;
            if (_noMessageIcon != null)
            {
                _noMessageIcon.Hidden = false;
            }

            if (_lockedIcon != null)
            {
                _lockedIcon.Hidden = true;
            }

            if (_lockedButton != null)
            {
                _lockedButton.interactable = false;
                _lockedButton.onClick.RemoveAllListeners();
            }
        }
    }
}
