using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Domain.Models;
using GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Domain.UseCases;
using GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Presentation.ViewModels;
using GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Presentation.Views;
using GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Presentation.Views;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Presentation.Presenters
{
    public class UnitEnhanceRankUpConfirmDialogPresenter : IUnitEnhanceRankUpConfirmDialogViewDelegate
    {
        [Inject] UnitEnhanceRankUpConfirmDialogViewController ViewController { get; }
        [Inject] UnitEnhanceRankUpConfirmDialogViewController.Argument Argument { get; }
        [Inject] GetUnitEnhanceRankUpConfirmModelUseCase GetUnitEnhanceRankUpConfirmModelUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }
        
        void IUnitEnhanceRankUpConfirmDialogViewDelegate.OnViewDidLoad()
        {
            var model = GetUnitEnhanceRankUpConfirmModelUseCase.GetModel(Argument.UserUnitId);
            var viewModel = TranslateViewModel(model);
            ViewController.Setup(viewModel);
        }
        
        void IUnitEnhanceRankUpConfirmDialogViewDelegate.OnViewDidAppear()
        {
            ViewController.PlayCostItemAppearanceAnimation();
        }

        void IUnitEnhanceRankUpConfirmDialogViewDelegate.OnConfirmButtonTapped()
        {
            ViewController.Dismiss();
            Argument.OnConfirm?.Invoke();
        }

        void IUnitEnhanceRankUpConfirmDialogViewDelegate.OnCancelButtonTapped()
        {
            ViewController.Dismiss();
        }

        void IUnitEnhanceRankUpConfirmDialogViewDelegate.OnRankUpDetailButtonTapped()
        {
            var args = new UnitEnhanceRankUpDetailDialogViewController.Argument(Argument.UserUnitId);
            var viewController = ViewFactory
                .Create<UnitEnhanceRankUpDetailDialogViewController, UnitEnhanceRankUpDetailDialogViewController.Argument>(args);
            ViewController.PresentModally(viewController);
        }

        UnitEnhanceRankUpConfirmViewModel TranslateViewModel(UnitEnhanceRankUpConfirmModel model)
        {
            var costItems = model.CostItems.Select(TranslateCostItemViewModel).ToList();

            var abilityViewModels = model.NewlyUnlockedUnitAbilities
                .Select(ability => new UnitEnhanceAbilityViewModel(ability, UnitLevel.Empty, false))
                .ToList();

            return new UnitEnhanceRankUpConfirmViewModel(
                costItems,
                model.RoleType,
                model.BeforeLimitLevel,
                model.AfterLimitLevel,
                model.BeforeHp,
                model.AfterHp,
                model.BeforeAttackPower,
                model.AfterAttackPower,
                abilityViewModels,
                model.UnitRankUpEnableConfirm
            );
        }

        UnitEnhanceCostItemViewModel TranslateCostItemViewModel(UnitEnhanceCostItemModel model)
        {
            return new UnitEnhanceCostItemViewModel(
                ItemViewModelTranslator.ToItemIconViewModel(model.Item),
                model.PossessionAmount
            );
        }
    }
}
