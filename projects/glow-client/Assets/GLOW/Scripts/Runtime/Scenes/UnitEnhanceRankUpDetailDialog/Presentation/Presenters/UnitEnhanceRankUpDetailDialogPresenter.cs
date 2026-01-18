using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.UnitEnhance.Presentation.Translators;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Domain.Models;
using GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Domain.UseCases;
using GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Presentation.ViewModels;
using GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Presentation.Presenters
{
    public class UnitEnhanceRankUpDetailDialogPresenter : IUnitEnhanceRankUpDetailDialogViewDelegate
    {
        [Inject] UnitEnhanceRankUpDetailDialogViewController ViewController { get; }
        [Inject] UnitEnhanceRankUpDetailDialogViewController.Argument Argument { get; }
        [Inject] UnitEnhanceRankUpDetailDialogUseCase UnitEnhanceRankUpDetailDialogUseCase { get; }

        void IUnitEnhanceRankUpDetailDialogViewDelegate.ViewDidLoad()
        {
            var model = UnitEnhanceRankUpDetailDialogUseCase.GetUnitEnhanceRankUpDetailDialogModel(Argument.UserUnitId);
            var viewModel = TranslateViewModel(model);
            ViewController.Setup(viewModel,(resourceType, masterDataId, playerResourceAmount) =>
            {
                ItemDetailUtil.Main.ShowItemDetailView(
                    resourceType,
                    masterDataId,
                    playerResourceAmount,
                    ViewController);
            });
        }

        void IUnitEnhanceRankUpDetailDialogViewDelegate.OnCloseButtonTapped()
        {
            ViewController.Dismiss();
        }

        UnitEnhanceRankUpDetailDialogViewModel TranslateViewModel(UnitEnhanceRankUpDetailDialogModel model)
        {
            var cellModelList = model.CellModelList.Select(TranslateCellViewModel).ToList();
            return new UnitEnhanceRankUpDetailDialogViewModel(cellModelList);
        }

        UnitEnhanceRankUpDetailCellViewModel TranslateCellViewModel(UnitEnhanceRankUpDetailCellModel model)
        {
            var requireItems = model.RequireItems
                .Select(UnitEnhanceViewModelTranslator.TranslateToRequireItemViewModel)
                .ToList();

            var unitEnhanceAbilityViewModels = model.NewlyUnlockedAbilities
                .Select(ability => ability.IsEmpty()
                    ? UnitEnhanceAbilityViewModel.Empty
                    : new UnitEnhanceAbilityViewModel(
                        ability,
                        UnitLevel.Empty,
                        false))
                .ToList();

            return new UnitEnhanceRankUpDetailCellViewModel(
                model.RoleType,
                model.LimitLevel,
                model.RequiredLevel,
                requireItems,
                model.Hp,
                model.AddHp,
                model.AttackPower,
                model.AddAttackPower,
                unitEnhanceAbilityViewModels,
                model.IsComplete);
        }
    }
}
