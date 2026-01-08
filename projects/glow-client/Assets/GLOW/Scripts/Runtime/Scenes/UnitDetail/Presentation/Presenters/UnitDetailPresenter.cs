using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.SpecialAttackInfo.Presentation.Views;
using GLOW.Scenes.UnitDetail.Domain.Models;
using GLOW.Scenes.UnitDetail.Domain.UseCases;
using GLOW.Scenes.UnitDetail.Presentation.ViewModels;
using GLOW.Scenes.UnitDetail.Presentation.Views;
using GLOW.Scenes.UnitEnhance.Domain.Contains;
using GLOW.Scenes.UnitEnhance.Domain.Models;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.UnitDetail.Presentation.Presenters
{
    public class UnitDetailPresenter : IUnitDetailViewDelegate
    {
        [Inject] IUnitDetailIViewController ViewController { get; }
        [Inject] UnitDetailViewController.Argument Argument { get; }
        [Inject] GetUnitMaxStatusDetailUseCase GetUnitMaxStatusDetailUseCase { get; }
        [Inject] GetUnitMinimumStatusDetailUseCase GetUnitMinimumStatusDetailUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }

        UnitGrade _unitGrade = UnitGrade.Empty;
        UnitLevel _unitLevel = UnitLevel.Empty;
        UnitEnhanceUnitInfoViewModel _unitInfoViewModel;
        UnitEnhanceTabType _currentTab = UnitEnhanceTabType.Status;

        void IUnitDetailViewDelegate.ViewDidLoad()
        {
            var model = GetDetailModel();
            _unitGrade = model.UnitGrade;
            _unitLevel = model.UnitLevel;
            var viewModel = TranslateViewModel(model);
            ViewController.Setup(viewModel);
            UpdateActiveAbilityTab();
            SetupCurrentTabType(_currentTab);
        }

        UnitDetailModel GetDetailModel()
        {
            if (Argument.IsMaxStatus)
            {
                return GetUnitMaxStatusDetailUseCase.GetUnitDetail(Argument.MstUnitId);
            }

            return GetUnitMinimumStatusDetailUseCase.GetUnitDetail(Argument.MstUnitId);
        }

        void IUnitDetailViewDelegate.OnBackButtonTapped()
        {
            HomeViewNavigation.TryPop();
        }

        void IUnitDetailViewDelegate.OnSpecialAttackDetailButtonTapped()
        {
            var argument = new SpecialAttackInfoViewController.Argument(Argument.MstUnitId, _unitGrade, _unitLevel);
            var viewController = ViewFactory.Create<SpecialAttackInfoViewController, SpecialAttackInfoViewController.Argument>(argument);
            ViewController.PresentModally(viewController);
        }

        void IUnitDetailViewDelegate.OnDetailTabButtonTapped()
        {
            SetupCurrentTabType(UnitEnhanceTabType.Detail);
        }

        void IUnitDetailViewDelegate.OnSpecialAttackTabButtonTapped()
        {
            SetupCurrentTabType(UnitEnhanceTabType.SpecialAttack);
        }

        void IUnitDetailViewDelegate.OnAbilityTabButtonTapped()
        {
            SetupCurrentTabType(UnitEnhanceTabType.Ability);
        }

        void IUnitDetailViewDelegate.OnStatusTabButtonTapped()
        {
            SetupCurrentTabType(UnitEnhanceTabType.Status);
        }

        UnitDetailViewModel TranslateViewModel(UnitDetailModel model)
        {
            _unitInfoViewModel = TranslateUnitInfoViewModel(model);
            var levelUp = TranslateLevelUpViewModel(model);
            return new UnitDetailViewModel(_unitInfoViewModel, levelUp, model.MaxStatusFlag);
        }

        UnitEnhanceLevelUpTabViewModel TranslateLevelUpViewModel(UnitDetailModel model)
        {
            return new UnitEnhanceLevelUpTabViewModel(
                model.RoleType,
                UnitEnhanceLevelUpViewModel.Empty,
                UnitEnhanceRankUpViewModel.Empty,
                model.Hp,
                model.AttackPower,
                model.UnitGrade,
                NotificationBadge.False
                );
        }

        UnitEnhanceAbilityViewModel TranslateAbilityViewModel(UnitEnhanceAbilityModel model)
        {
            return new UnitEnhanceAbilityViewModel(model.Ability, model.UnlockUnitLevel, model.IsLock);
        }

        UnitEnhanceUnitInfoViewModel TranslateUnitInfoViewModel(UnitDetailModel model)
        {
            return new UnitEnhanceUnitInfoViewModel(
                model.UnitImageAssetPath,
                model.Name,
                model.RoleType,
                model.Rarity,
                model.UnitLevel,
                model.UnitLevelLimit,
                model.SeriesLogoImagePath,
                model.SummonCoolTime,
                model.SummonCost,
                model.Color,
                TranslateSpecialAttackViewModel(model.SpecialAttack),
                TranslateUnitDetailViewModel(model.DetailModel),
                TranslateAbilityViewModelList(model.Abilities),
                TranslateUnitStatusViewModel(model.StatusModel, model.RoleType));
        }

        UnitEnhanceSpecialAttackViewModel TranslateSpecialAttackViewModel(UnitEnhanceSpecialAttackModel model)
        {
            return new UnitEnhanceSpecialAttackViewModel(
                model.Name,
                model.Description,
                model.InitialCoolTime,
                model.CoolTime,
                model.RoleType);
        }

        UnitEnhanceUnitDetailViewModel TranslateUnitDetailViewModel(UnitEnhanceUnitDetailModel model)
        {
            return new UnitEnhanceUnitDetailViewModel(model.Detail);
        }

        IReadOnlyList<UnitEnhanceAbilityViewModel> TranslateAbilityViewModelList(
            IReadOnlyList<UnitEnhanceAbilityModel> modelList)
        {
            return modelList.Select(TranslateAbilityViewModel).ToList();
        }

        UnitEnhanceUnitStatusViewModel TranslateUnitStatusViewModel(UnitEnhanceUnitStatusModel model, CharacterUnitRoleType roleType)
        {
            return new UnitEnhanceUnitStatusViewModel(
                roleType,
                model.Hp,
                model.AttackPower,
                model.AttackRange,
                model.MoveSpeed);
        }

        void SetupUnitDetail()
        {
            ViewController.SetUnitDetail(_unitInfoViewModel.DetailModel);
        }

        void SetupSpecialAttack()
        {
            ViewController.SetSpecialAttack(_unitInfoViewModel.SpecialAttack);
        }

        void SetupAbility()
        {
            ViewController.SetAbility(_unitInfoViewModel.AbilityModelList);
        }

        void SetupStatus()
        {
            ViewController.SetStatus(_unitInfoViewModel.StatusModel);
        }

        void UpdateActiveAbilityTab()
        {
            ViewController.SetupActiveAbilityTab(_unitInfoViewModel.RoleType != CharacterUnitRoleType.Special);
        }

        void SetupCurrentTabType(UnitEnhanceTabType currentTab)
        {
            _currentTab = currentTab;
            switch (currentTab)
            {
                case UnitEnhanceTabType.Detail:
                    SetupUnitDetail();
                    break;
                case UnitEnhanceTabType.SpecialAttack:
                    SetupSpecialAttack();
                    break;
                case UnitEnhanceTabType.Ability:
                    SetupAbility();
                    break;
                case UnitEnhanceTabType.Status:
                    SetupStatus();
                    break;
                default:
                    throw new ArgumentOutOfRangeException();
            }
        }
    }
}
