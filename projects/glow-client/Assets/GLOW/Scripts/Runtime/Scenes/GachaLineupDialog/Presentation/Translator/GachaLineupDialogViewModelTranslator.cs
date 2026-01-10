using System;
using System.Linq;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.GachaLineupDialog.Domain.Models;
using GLOW.Scenes.GachaLineupDialog.Presentation.ViewModels;
using GLOW.Scenes.GachaRatio.Domain.Model;

namespace GLOW.Scenes.GachaLineupDialog.Presentation.Translator
{
    public class GachaLineupDialogViewModelTranslator
    {
        public static GachaLineupDialogViewModel TranslateToViewModel(
            GachaLineupDialogUseCaseModel model,
            Action<GachaRatioResourceModel> onClickIcon)
        {
            return new GachaLineupDialogViewModel(
                ToRatioPageViewModel(model.NormalPrizePageModel, onClickIcon),
                ToRatioPageViewModel(model.NormalPrizeInFixedPageModel, onClickIcon),
                ToRatioPageViewModel(model.UpperPrizeInMaxRarityPageModel, onClickIcon),
                ToRatioPageViewModel(model.UpperPrizeInPickupPageModel, onClickIcon),
                model.GachaFixedPrizeDescription
            );
        }

        static GachaLineupPageViewModel ToRatioPageViewModel(GachaLineupPageModel model, Action<GachaRatioResourceModel> onClickIcon)
        {
            if (model.IsEmpty()) return GachaLineupPageViewModel.Empty;

            return new GachaLineupPageViewModel(
                ToRatioLineupListViewModel(model.GachaLineupPickupListModel, onClickIcon),
                ToRatioLineupListViewModel(model.GachaLineupListModel, onClickIcon)
            );
        }

        static GachaLineupListViewModel ToRatioLineupListViewModel(
            GachaLineupListModel model,
            Action<GachaRatioResourceModel> onClickIcon)
        {
            return new GachaLineupListViewModel(
                ToRatioLineupViewModel(model.URareLineupModel, onClickIcon),
                ToRatioLineupViewModel(model.SSRareLineupModel, onClickIcon),
                ToRatioLineupViewModel(model.SRareLineupModel, onClickIcon),
                ToRatioLineupViewModel(model.RLineupModel, onClickIcon)
            );
        }

        static GachaLineupCellListViewModel ToRatioLineupViewModel(GachaLineupCellListModel model, Action<GachaRatioResourceModel> onClickIcon)
        {
            return new GachaLineupCellListViewModel(
                model.RatioProbabilityAmount,
                model.GachaLineupCellModels
                    .Select(cell => new GachaLineupCellViewModel(
                        cell.ResourceModel,
                        PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(cell.PlayerResourceModel),
                        cell.CharacterName,
                        cell.ResourceName,
                        cell.NumberParity,
                        onClickIcon
                    ))
                    .ToList()
            );
        }
    }
}