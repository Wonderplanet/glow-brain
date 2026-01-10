using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Modules.Spine.Presentation;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using GLOW.Scenes.UnitEnhance.Presentation.Views.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.UnitDetail.Presentation.Views
{
    public class UnitDetailView : UIView
    {
        [SerializeField] UISpineWithOutlineAvatar _avatar;
        [SerializeField] AvatarFooterShadowComponent _avatarShadow;
        [SerializeField] UnitEnhanceUnitInfoComponent _unitInfo;
        [SerializeField] IconCharaGrade _grade;
        [SerializeField] UIText _maxStatusText;
        [SerializeField] UIText _unitAcquisitionText;
        [Header("キャラ情報")]
        [SerializeField] UnitEnhanceUnitDetailComponent _unitDetailRoot;

        [Header("必殺ワザ")]
        [SerializeField] UnitEnhanceSpecialAttackDetailComponent _specialAttackRoot;

        [Header("特性")]
        [SerializeField] UnitEnhanceAbilityComponent _abilityRoot;

        [Header("ステータス")]
        [SerializeField] UnitEnhanceUnitStatusComponent _statusRoot;

        [Header("4タブ")]
        [SerializeField] UIToggleableComponentGroup _infoToggleableComponentGroup;
        [SerializeField] GameObject _abilityTabButton;

        public UISpineWithOutlineAvatar Avatar => _avatar;
        public AvatarFooterShadowComponent AvatarShadow => _avatarShadow;
        public UnitEnhanceUnitInfoComponent UnitInfo => _unitInfo;

        public void SetLevelUp(UnitEnhanceLevelUpTabViewModel tabViewModel)
        {
            _grade.SetGrade(tabViewModel.UnitGrade);
        }

        public void SetupActiveAbilityTab(bool isActive)
        {
            _abilityTabButton.SetActive(isActive);
        }

        public void SetUnitDetail(UnitEnhanceUnitDetailViewModel viewModel)
        {
            _infoToggleableComponentGroup.SetToggleOn("Description");

            _unitDetailRoot.Hidden = false;
            _specialAttackRoot.Hidden = true;
            _abilityRoot.Hidden = true;
            _statusRoot.Hidden = true;

            _unitDetailRoot.Setup(viewModel);
        }

        public void SetSpecialAttack(UnitEnhanceSpecialAttackViewModel viewModel)
        {
            _infoToggleableComponentGroup.SetToggleOn("Special");

            _unitDetailRoot.Hidden = true;
            _specialAttackRoot.Hidden = false;
            _abilityRoot.Hidden = true;
            _statusRoot.Hidden = true;

            _specialAttackRoot.Setup(viewModel);
        }

        public void SetAbility(IReadOnlyList<UnitEnhanceAbilityViewModel> viewModelList)
        {
            _infoToggleableComponentGroup.SetToggleOn("Ability");

            _unitDetailRoot.Hidden = true;
            _specialAttackRoot.Hidden = true;
            _abilityRoot.Hidden = false;
            _statusRoot.Hidden = true;

            _abilityRoot.Setup(viewModelList);
        }

        public void SetStatus(UnitEnhanceUnitStatusViewModel viewModel)
        {
            _infoToggleableComponentGroup.SetToggleOn("Status");

            _unitDetailRoot.Hidden = true;
            _specialAttackRoot.Hidden = true;
            _abilityRoot.Hidden = true;
            _statusRoot.Hidden = false;

            _statusRoot.Setup(viewModel);
        }

        public void PlayAttackAnimation()
        {
            if (_avatar.GetCurrentAnimationStateName() == CharacterUnitAnimation.Wait.Name)
            {
                _avatar.Animate(CharacterUnitAnimation.Attack.Name, CharacterUnitAnimation.Wait.Name);
            }
        }

        public void SetActiveMaxStatusText(MaxStatusFlag maxStatusFlag)
        {
            _maxStatusText.Hidden = !maxStatusFlag;

            // 最大ステータス以外はキャラ獲得テキストを表示する
            _unitAcquisitionText.Hidden = maxStatusFlag;
        }
    }
}
