using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.Translators;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.ItemBox.Presentation.Presenters;
using GLOW.Scenes.ItemBox.Presentation.Views;
using GLOW.Scenes.SpecialAttackInfo.Presentation.Views;
using GLOW.Scenes.UnitEnhance.Domain.Contains;
using GLOW.Scenes.UnitEnhance.Domain.Models;
using GLOW.Scenes.UnitEnhance.Domain.UseCases;
using GLOW.Scenes.UnitEnhance.Presentation.Translators;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using GLOW.Scenes.UnitEnhance.Presentation.Views;
using GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Presentation.Views;
using GLOW.Scenes.UnitEnhanceGradeUpDialog.Presentation.Views;
using GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Presentation.Views;
using GLOW.Scenes.UnitEnhanceRankUpDialog.Presentation.Views;
using GLOW.Scenes.UnitLevelUpDialogView.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.UnitEnhance.Presentation.Presentations
{
    public class UnitEnhancePresenter : IUnitEnhanceViewDelegate
    {
        [Inject] UnitViewController ViewController { get; }
        [Inject] UnitViewController.Argument Argument { get; }
        [Inject] GetUnitEnhanceAvatarListUseCase GetUnitEnhanceAvatarListUseCase { get; }
        [Inject] GetUnitEnhanceLevelUpUseCase GetUnitEnhanceLevelUpUseCase { get; }
        [Inject] GetUnitEnhanceUnitInfoUseCase GetUnitEnhanceUnitInfoUseCase { get; }
        [Inject] GetUnitEnhanceGradeUpUseCase GetUnitEnhanceGradeUpUseCase { get; }
        [Inject] ExecuteUnitRankUpUseCase ExecuteUnitRankUpUseCase { get; }
        [Inject] GetUnitEnhanceSpecialAttackInfoUseCase GetUnitEnhanceSpecialAttackInfoUseCase { get; }
        [Inject] ExecuteGradeUpUseCase ExecuteUnitGradeUpUseCase { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }

        UserDataId _currentUnitId;
        UnitEnhanceUnitInfoViewModel _unitInfoViewModel;
        UnitEnhanceTabType _currentTab = UnitEnhanceTabType.Status;

        void IUnitEnhanceViewDelegate.ViewDidLoad()
        {
            _currentUnitId = Argument.UserUnitId;
            SetupAvatarList();
            SetupLevelUp();
            UpdateUnitInfo();
            UpdateActiveAbilityTab();
            SetupCurrentTabType(_currentTab, _unitInfoViewModel.RoleType);
        }

        void IUnitEnhanceViewDelegate.OnBackButtonTapped()
        {
            HomeViewNavigation.TryPop();
        }

        void IUnitEnhanceViewDelegate.OnEnhanceTabButtonTapped()
        {
            SetupLevelUp();
        }

        void IUnitEnhanceViewDelegate.OnGradeUpTabButtonTapped()
        {
            SetupGradeUp();
        }

        void IUnitEnhanceViewDelegate.OnDetailTabButtonTapped()
        {
            SetupCurrentTabType(UnitEnhanceTabType.Detail, _unitInfoViewModel.RoleType);
        }

        void IUnitEnhanceViewDelegate.OnSpecialAttackTabButtonTapped()
        {
            SetupCurrentTabType(UnitEnhanceTabType.SpecialAttack, _unitInfoViewModel.RoleType);
        }

        void IUnitEnhanceViewDelegate.OnAbilityTabButtonTapped()
        {
            SetupCurrentTabType(UnitEnhanceTabType.Ability, _unitInfoViewModel.RoleType);
        }

        void IUnitEnhanceViewDelegate.OnStatusTabButtonTapped()
        {
            SetupCurrentTabType(UnitEnhanceTabType.Status, _unitInfoViewModel.RoleType);
        }

        void IUnitEnhanceViewDelegate.OnLevelUpButtonTapped()
        {
            var args = new UnitLevelUpDialogViewController.Argument(_currentUnitId, isLevelUp =>
            {
                if (isLevelUp)
                {
                    SetupLevelUpWithAnimation();
                }
                UpdateUnitInfo();
                UpdateActiveAbilityTab();
                SetupLevelUp();
                SetupCurrentTabType(_currentTab, _unitInfoViewModel.RoleType);
            });
            var viewController = ViewFactory.Create<UnitLevelUpDialogViewController, UnitLevelUpDialogViewController.Argument>(args);
            ViewController.PresentModally(viewController);
        }

        void IUnitEnhanceViewDelegate.OnRankUpButtonTapped()
        {
            // ランクアップ確認ダイアログ
            var confirmArgs = new UnitEnhanceRankUpConfirmDialogViewController.Argument(_currentUnitId, () =>
            {
                DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async cancellationToken =>
                {
                    var result = await ExecuteUnitRankUpUseCase.ExecuteRankUp(cancellationToken, _currentUnitId);
                    var args = new UnitEnhanceRankUpDialogViewController.Argument(result.UserUnitId, result.BeforeRank, result.AfterRank);
                    var viewController = ViewFactory.Create<UnitEnhanceRankUpDialogViewController, UnitEnhanceRankUpDialogViewController.Argument>(args);
                    ViewController.PresentModally(viewController);
                    UpdateUnitInfo();
                    UpdateActiveAbilityTab();
                    SetupLevelUp();
                    SetupCurrentTabType(_currentTab, _unitInfoViewModel.RoleType);
                });
            });

            var confirmViewController = ViewFactory.Create<UnitEnhanceRankUpConfirmDialogViewController, UnitEnhanceRankUpConfirmDialogViewController.Argument>(confirmArgs);
            ViewController.PresentModally(confirmViewController);
        }

        void ShowLackItemMessageView(ItemName itemName, ItemAmount currentAmount, ItemAmount requireAmount)
        {
            var lackItemAmount = requireAmount - currentAmount;
            MessageViewUtil.ShowMessageWithClose("キャラ強化", $"所持してる{itemName}が{lackItemAmount}個不足しています。");
        }

        void ShowConfirmMessageView(string eventText, ItemName itemName, ItemAmount currentAmount, ItemAmount requireAmount, Action onConfirm)
        {
            var consumedAmount = currentAmount - requireAmount;
            var messageText =
                $"{itemName}を{requireAmount}個使用して{eventText}しますか？\n\n所持数{currentAmount} → {consumedAmount}";
            MessageViewUtil.ShowMessageWith2Buttons(
                "キャラ強化",
                messageText,
                "",
                eventText,
                "キャンセル",
                onConfirm);
        }

        void IUnitEnhanceViewDelegate.OnGradeUpButtonTapped()
        {
            // グレードアップ確認ダイアログ
            var confirmArgument = new UnitEnhanceGradeUpConfirmDialogViewController.Argument(_currentUnitId, () =>
            {
                DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async cancellationToken =>
                {
                    var result = await ExecuteUnitGradeUpUseCase.ExecuteGradeUp(cancellationToken, _currentUnitId);
                    var args = new UnitEnhanceGradeUpDialogViewController.Argument(result.UserUnitId, result.BeforeGrade, result.AfterGrade);
                    var viewController = ViewFactory.Create<UnitEnhanceGradeUpDialogViewController, UnitEnhanceGradeUpDialogViewController.Argument>(args);
                    ViewController.PresentModally(viewController);
                    UpdateUnitInfo();
                    UpdateActiveAbilityTab();
                    SetupGradeUp();
                    SetupCurrentTabType(_currentTab, _unitInfoViewModel.RoleType);
                });
            });
            var confirmViewController = ViewFactory.Create<UnitEnhanceGradeUpConfirmDialogViewController, UnitEnhanceGradeUpConfirmDialogViewController.Argument>(confirmArgument);
            ViewController.PresentModally(confirmViewController);
        }

        void IUnitEnhanceViewDelegate.OnSpecialAttackDetailButtonTapped()
        {
            var model = GetUnitEnhanceSpecialAttackInfoUseCase.GetUnitEnhanceSpecialAttackInfoModel(_currentUnitId);
            var argument = new SpecialAttackInfoViewController.Argument(model.MstUnitId, model.UnitGrade, model.UnitLevel);
            var controller = ViewFactory.Create<SpecialAttackInfoViewController, SpecialAttackInfoViewController.Argument>(argument);
            ViewController.PresentModally(controller);
        }

        void IUnitEnhanceViewDelegate.SwitchUnit(MasterDataId mstUnitId)
        {
            _currentUnitId = GetUnitEnhanceUnitInfoUseCase.GetUserUnitId(mstUnitId);
            SetupLevelUp();
            UpdateUnitInfo();
            UpdateActiveAbilityTab();
            SetupCurrentTabType(_currentTab, _unitInfoViewModel.RoleType);
        }

        void SetupAvatarList()
        {
            var model = GetUnitEnhanceAvatarListUseCase.GetAvatarList(Argument.UnitList, Argument.UserUnitId);
            var viewModel = TranslateAvatarListViewModel(model);
            ViewController.SetupAvatarList(viewModel);
        }

        void SetupLevelUp()
        {
            var model = GetUnitEnhanceLevelUpUseCase.GetLevelUpModel(_currentUnitId);
            var viewModel = UnitEnhanceViewModelTranslator.TranslateToLevelUpTabViewModel(model);
            ViewController.SetupLevelUpTab(
                viewModel,
                ShowItemDetailView);
        }

        void SetupLevelUpWithAnimation()
        {
            var model = GetUnitEnhanceLevelUpUseCase.GetLevelUpModel(_currentUnitId);
            var viewModel = UnitEnhanceViewModelTranslator.TranslateToLevelUpTabViewModel(model);
            ViewController.SetupLevelUpWithAnimation(
                viewModel,
                ShowItemDetailView);
        }

        void SetupGradeUp()
        {
            var model = GetUnitEnhanceGradeUpUseCase.GetGradeUpModel(_currentUnitId);
            var viewModel = TranslateGradeUpViewModel(model);
            ViewController.SetupGradeUpTab(viewModel);
        }

        void UpdateUnitInfo()
        {
            var model = GetUnitEnhanceUnitInfoUseCase.GetUnitInfo(_currentUnitId);
            _unitInfoViewModel = TranslateUnitInfoViewModel(model);
            ViewController.SetupUnitInfo(_unitInfoViewModel);
        }

        void UpdateActiveAbilityTab()
        {
            ViewController.SetupActiveAbilityTab(_unitInfoViewModel.RoleType != CharacterUnitRoleType.Special);
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

        UnitEnhanceAvatarListViewModel TranslateAvatarListViewModel(UnitEnhanceAvatarListModel model)
        {
            return new UnitEnhanceAvatarListViewModel(model.UnitList, model.PresentationUnitId);
        }

        UnitEnhanceAbilityViewModel TranslateAbilityViewModel(UnitEnhanceAbilityModel model)
        {
            return new UnitEnhanceAbilityViewModel(model.Ability, model.UnlockUnitLevel, model.IsLock);
        }

        UnitEnhanceGradeUpTabViewModel TranslateGradeUpViewModel(UnitEnhanceGradeUpModel model)
        {
            return new UnitEnhanceGradeUpTabViewModel(
                ItemViewModelTranslator.ToItemIconViewModel(model.RequireItemIconModel),
                model.RequireItemAmount,
                model.PossessionItemAmount,
                model.ItemName,
                model.BeforeHp,
                model.AfterHp,
                model.BeforeAttackPower,
                model.AfterAttackPower,
                model.UnitGrade,
                model.IsGradeUp
            );
        }

        UnitEnhanceUnitInfoViewModel TranslateUnitInfoViewModel(UnitEnhanceUnitInfoModel model)
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
                TranslateAbilityViewModelList(model.AbilityModelList),
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

        void SetupCurrentTabType(UnitEnhanceTabType currentTab, CharacterUnitRoleType roleType)
        {
            _currentTab = currentTab;

            // スペシャルキャラには特性がないため、特性タブは表示しない
            if (roleType == CharacterUnitRoleType.Special && _currentTab == UnitEnhanceTabType.Ability)
            {
                _currentTab = UnitEnhanceTabType.Status;
            }

            switch (_currentTab)
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

        void ShowItemDetailView(
            ResourceType resourceType,
            MasterDataId masterDataId,
            PlayerResourceAmount playerResourceAmount)
        {
            ItemDetailUtil.Main.ShowItemDetailView(
                resourceType,
                masterDataId,
                playerResourceAmount,
                ViewController);
        }
    }
}
