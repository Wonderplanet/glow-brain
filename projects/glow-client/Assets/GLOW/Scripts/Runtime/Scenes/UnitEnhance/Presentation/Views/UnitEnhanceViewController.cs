using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using GLOW.Modules.UnitAvatarPageView.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Views;
using UIKit;
using WPFramework.Exceptions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.UnitEnhance.Presentation.Views
{
    public class UnitViewController : HomeBaseViewController<UnitEnhanceView>,
        IUnitAvatarPageListDelegate
    {
        public record Argument(UserDataId UserUnitId, IReadOnlyList<UserDataId> UnitList);

        [Inject] IUnitEnhanceViewDelegate ViewDelegate { get; }
        [Inject] Argument Args { get; }
        [Inject] IViewFactory ViewFactory { get; }

        UnitEnhanceLevelUpTabViewModel _levelUpTabViewModel;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.ViewDidLoad();
        }

        public void SetupAvatarList(UnitEnhanceAvatarListViewModel viewModel)
        {
            ActualView.AvatarPageList.Delegate = this;
            ActualView.AvatarPageList.Setup(
                ViewFactory,
                this,
                viewModel.UnitList,
                viewModel.PresentationUnitId);
        }

        public void SetupUnitInfo(UnitEnhanceUnitInfoViewModel viewModel)
        {
            ActualView.UnitInfo.Setup(viewModel);
        }

        public void SetupActiveAbilityTab(bool isActive)
        {
            ActualView.SetupActiveAbilityTab(isActive);
        }

        public void SetUnitDetail(UnitEnhanceUnitDetailViewModel viewModel)
        {
            ActualView.SetUnitDetail(viewModel);
        }

        public void SetSpecialAttack(UnitEnhanceSpecialAttackViewModel viewModel)
        {
            ActualView.SetSpecialAttack(viewModel);
        }

        public void SetAbility(IReadOnlyList<UnitEnhanceAbilityViewModel> viewModel)
        {
            ActualView.SetAbility(viewModel);
        }

        public void SetStatus(UnitEnhanceUnitStatusViewModel viewModel)
        {
            ActualView.SetStatus(viewModel);
        }

        public void SetupLevelUpWithAnimation(
            UnitEnhanceLevelUpTabViewModel newTabViewModel,
            Action<ResourceType, MasterDataId, PlayerResourceAmount> onItemTapped)
        {
            var hp = newTabViewModel.Hp - _levelUpTabViewModel.Hp;
            var attackPower = newTabViewModel.AttackPower - _levelUpTabViewModel.AttackPower;
            if (newTabViewModel.RoleType == CharacterUnitRoleType.Special)
            {
                ActualView.ShowSpecialUnitLevelUpAnimation(attackPower);
            }
            else
            {
                ActualView.ShowLevelUpAnimation(hp, attackPower);
            }
            ActualView.AvatarPageList.ShowLevelUpAnimation();

            SetupLevelUpTab(newTabViewModel, onItemTapped);
        }

        public void SetupLevelUpTab(
            UnitEnhanceLevelUpTabViewModel tabViewModel,
            Action<ResourceType, MasterDataId, PlayerResourceAmount> onItemTapped)
        {
            _levelUpTabViewModel = tabViewModel;
            ActualView.SetLevelUp(tabViewModel,onItemTapped);
        }

        public void SetupGradeUpTab(UnitEnhanceGradeUpTabViewModel tabViewModel)
        {
            ActualView.SetGradeUp(tabViewModel);
        }

        void IUnitAvatarPageListDelegate.SwitchUnit(MasterDataId mstUnitId)
        {
            ViewDelegate.SwitchUnit(mstUnitId);
        }

        void IUnitAvatarPageListDelegate.WillTransitionTo()
        {
            ActualView.UserInteraction = false;
        }

        void IUnitAvatarPageListDelegate.DidFinishAnimating(bool finished, bool transitionCompleted)
        {
            ActualView.UserInteraction = finished;
        }

        [UIAction]
        void OnBackButtonTapped()
        {
            ViewDelegate.OnBackButtonTapped();
        }

        [UIAction]
        void OnEnhanceTabButtonTapped()
        {
            ViewDelegate.OnEnhanceTabButtonTapped();
        }

        [UIAction]
        void OnGradeUpUTabButtonTapped()
        {
            ViewDelegate.OnGradeUpTabButtonTapped();
        }

        [UIAction]
        void OnDetailTabButtonTapped()
        {
            ViewDelegate.OnDetailTabButtonTapped();
        }

        [UIAction]
        void OnSpecialAttackTabButtonTapped()
        {
            ViewDelegate.OnSpecialAttackTabButtonTapped();
        }

        [UIAction]
        void OnAbilityTabButtonTapped()
        {
            ViewDelegate.OnAbilityTabButtonTapped();
        }

        [UIAction]
        void OnStatusTabButtonTapped()
        {
            ViewDelegate.OnStatusTabButtonTapped();
        }

        [UIAction]
        void OnSpecialAttackDetailButtonTapped()
        {
            ViewDelegate.OnSpecialAttackDetailButtonTapped();
        }

        [UIAction]
        void OnLevelUpButtonTapped()
        {
            ViewDelegate.OnLevelUpButtonTapped();
        }

        [UIAction]
        void OnRankUpButtonTapped()
        {
            ViewDelegate.OnRankUpButtonTapped();
        }

        [UIAction]
        void OnGradeUpButtonTapped()
        {
            ViewDelegate.OnGradeUpButtonTapped();
        }

        [UIAction]
        void OnLibraryButtonTapped()
        {
            NotImpl.Handle();
        }

        [UIAction]
        void OnRightArrowButtonTapped()
        {
            ActualView.AvatarPageList.ScrollToNextPage(scrollFinishSeSuppression:true);
        }

        [UIAction]
        void OnLeftArrowButtonTapped()
        {
            ActualView.AvatarPageList.ScrollToPrevPage(scrollFinishSeSuppression:true);
        }
    }
}
