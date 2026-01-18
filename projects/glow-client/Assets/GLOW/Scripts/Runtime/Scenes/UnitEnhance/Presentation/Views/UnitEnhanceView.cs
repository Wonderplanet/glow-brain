using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.UnitEnhance.Presentation.Views.Components;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Modules.UnitAvatarPageView.Presentation.Views;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.Serialization;

namespace GLOW.Scenes.UnitEnhance.Presentation.Views
{
    public class UnitEnhanceView : UIView
    {
        [SerializeField] UnitAvatarPageListComponent _avatarPageListComponent;
        [SerializeField] UnitEnhanceUnitInfoComponent _unitInfo;
        [SerializeField] UnitEnhanceLevelUpComponent _levelup;
        [SerializeField] UnitEnhanceRankUpComponent _rankup;
        [SerializeField] GameObject _noEnhanceButton;
        [SerializeField] IconCharaGrade _grade;
        [SerializeField] UnitEnhanceGradeUpComponent _gradeUp;
        [SerializeField] UnitEnhanceLevelUpStatusAnimationComponent _levelUpStatusAnimation;

        [FormerlySerializedAs("_unitDescriptionRoot")]
        [Header("ステータス")]
        [SerializeField] UnitEnhanceUnitStatusComponent _statusRoot;

        [Header("必殺ワザ")]
        [SerializeField] UnitEnhanceSpecialAttackDetailComponent _specialAttackRoot;

        [Header("特性")]
        [SerializeField] UnitEnhanceAbilityComponent _abilityRoot;

        [Header("ワンポイント")]
        [SerializeField] UnitEnhanceUnitDetailComponent _unitDetailRoot;

        [Header("4タブ")]
        [SerializeField] UIToggleableComponentGroup _infoToggleableComponentGroup;
        [Header("2タブ")]
        [SerializeField] UIToggleableComponentGroup _enhanceToggleableComponentGroup;
        [SerializeField] GameObject _abilityTabButton;
        [SerializeField] GameObject _gradeUpBadge;

        public UnitAvatarPageListComponent AvatarPageList => _avatarPageListComponent;
        public UnitEnhanceUnitInfoComponent UnitInfo => _unitInfo;

        const string TabToggleLevelUp = "LevelUp";
        const string TabToggleGradeUp = "GradeUp";

        public void SetLevelUp(
            UnitEnhanceLevelUpTabViewModel tabViewModel,
            Action<ResourceType, MasterDataId, PlayerResourceAmount> onItemTapped)
        {
            _levelup.Hidden = tabViewModel.LevelUp.IsEmpty();
            _rankup.Hidden = tabViewModel.RankUp.IsEmpty();
            _gradeUp.Hidden = true;
            _noEnhanceButton.SetActive(false);

            if (!_levelup.Hidden)
            {
                _levelup.Setup(tabViewModel.LevelUp);
            }
            else if (!_rankup.Hidden)
            {
                _rankup.Setup(tabViewModel.RankUp, onItemTapped);
            }
            else
            {
                _noEnhanceButton.SetActive(true);
            }
            SetCommonUI(TabToggleLevelUp, tabViewModel.IsGradeUp, tabViewModel.UnitGrade);
        }

        public void SetGradeUp(UnitEnhanceGradeUpTabViewModel tabViewModel)
        {
            _levelup.Hidden = true;
            _rankup.Hidden = true;
            _gradeUp.Hidden = false;
            _noEnhanceButton.SetActive(false);

            _gradeUp.Setup(tabViewModel);
            SetCommonUI(TabToggleGradeUp, tabViewModel.IsGradeUp, tabViewModel.UnitGrade);
        }

        void SetCommonUI(string tabToggle, NotificationBadge gradeUp, UnitGrade unitGrade)
        {
            SetupGrade(unitGrade);
            _enhanceToggleableComponentGroup.SetToggleOn(tabToggle);
            _levelUpStatusAnimation.EndAnimation();
            _gradeUpBadge.SetActive(gradeUp.Value);
        }

        public void SetupActiveAbilityTab(bool isActive)
        {
            _abilityTabButton.SetActive(isActive);
        }

        public void SetUnitDetail(UnitEnhanceUnitDetailViewModel viewModel)
        {
            _infoToggleableComponentGroup.SetToggleOn("Description");

            _statusRoot.Hidden = true;
            _specialAttackRoot.Hidden = true;
            _abilityRoot.Hidden = true;
            _unitDetailRoot.Hidden = false;

            _unitDetailRoot.Setup(viewModel);
        }

        public void SetSpecialAttack(UnitEnhanceSpecialAttackViewModel viewModel)
        {
            _infoToggleableComponentGroup.SetToggleOn("Special");

            _statusRoot.Hidden = true;
            _specialAttackRoot.Hidden = false;
            _abilityRoot.Hidden = true;
            _unitDetailRoot.Hidden = true;

            _specialAttackRoot.Setup(viewModel);
        }

        public void SetAbility(IReadOnlyList<UnitEnhanceAbilityViewModel> viewModelList)
        {
            _infoToggleableComponentGroup.SetToggleOn("Ability");

            _statusRoot.Hidden = true;
            _specialAttackRoot.Hidden = true;
            _abilityRoot.Hidden = false;
            _unitDetailRoot.Hidden = true;

            _abilityRoot.Setup(viewModelList);
        }

        public void SetStatus(UnitEnhanceUnitStatusViewModel viewModel)
        {
            _infoToggleableComponentGroup.SetToggleOn("Status");

            _statusRoot.Hidden = false;
            _specialAttackRoot.Hidden = true;
            _abilityRoot.Hidden = true;
            _unitDetailRoot.Hidden = true;

            _statusRoot.Setup(viewModel);
        }

        public void ShowLevelUpAnimation(HP addHp, AttackPower addAttackPower)
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_031_003);
            _levelUpStatusAnimation.PlayAnimation(addHp, addAttackPower);
        }

        public void ShowSpecialUnitLevelUpAnimation(AttackPower rushPower)
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_031_003);
            _levelUpStatusAnimation.PlaySpecialUnitAnimation(rushPower);
        }

        void SetupGrade(UnitGrade grade)
        {
            _grade.SetGrade(grade);
        }
    }
}
