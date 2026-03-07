using System;
using System.Linq;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.GachaRatio.Domain.Model;
using GLOW.Scenes.GachaRatio.Presentation.ViewModels;
using GLOW.Scenes.StepupGachaRatioDialog.Domain.Models;
using GLOW.Scenes.StepupGachaRatioDialog.Presentation.ViewModels;

namespace GLOW.Scenes.StepupGachaRatioDialog.Presentation.Translator
{
    public class StepupGachaRatioDialogViewModelTranslator
    {
        public static StepupGachaRatioDialogViewModel TranslateToViewModel(
            StepupGachaRatioDialogUseCaseModel useCaseModel,
            Action<GachaRatioResourceModel> onClickIcon)
        {
            var stepViewModels = useCaseModel.StepUseCaseModels
                .Select(stepModel => ToStepViewModel(stepModel, onClickIcon))
                .ToList();

            return new StepupGachaRatioDialogViewModel(stepViewModels);
        }

        static StepupGachaRatioStepViewModel ToStepViewModel(
            StepupGachaRatioStepUseCaseModel stepModel,
            Action<GachaRatioResourceModel> onClickIcon)
        {
            return new StepupGachaRatioStepViewModel(
                stepModel.StepNumber,
                stepModel.GachaFixedPrizeDescription,
                ToRatioPageViewModel(stepModel.NormalPrizePageModel, onClickIcon),
                ToRatioPageViewModel(stepModel.FixedPrizePageModel, onClickIcon));
        }

        static GachaRatioPageViewModel ToRatioPageViewModel(
            GachaRatioPageModel model,
            Action<GachaRatioResourceModel> onClickIcon)
        {
            if (model.IsEmpty()) return GachaRatioPageViewModel.Empty;

            return new GachaRatioPageViewModel(
                ToRarityRatioViewModel(model.RarityRatioModel),
                ToRatioLineupListViewModel(model.GachaRatioPickupListModel, onClickIcon),
                ToRatioLineupListViewModel(model.GachaRatioLineupListModel, onClickIcon));
        }

        static GachaRatioByRarityViewModel ToRarityRatioViewModel(GachaRatioRarityRatioModel model)
        {
            return new GachaRatioByRarityViewModel(
                new GachaRatioRarityRatioItemViewModel(model.UR.Rarity, model.UR.Probability),
                new GachaRatioRarityRatioItemViewModel(model.SSR.Rarity, model.SSR.Probability),
                new GachaRatioRarityRatioItemViewModel(model.SR.Rarity, model.SR.Probability),
                new GachaRatioRarityRatioItemViewModel(model.R.Rarity, model.R.Probability));
        }

        static GachaRatioLineupListViewModel ToRatioLineupListViewModel(
            GachaRatioLineupListModel model,
            Action<GachaRatioResourceModel> onClickIcon)
        {
            return new GachaRatioLineupListViewModel(
                ToRatioLineupViewModel(model.URareLineupModel, onClickIcon),
                ToRatioLineupViewModel(model.SSRareLineupModel, onClickIcon),
                ToRatioLineupViewModel(model.SRareLineupModel, onClickIcon),
                ToRatioLineupViewModel(model.RLineupModel, onClickIcon));
        }

        static GachaRatioLineupViewModel ToRatioLineupViewModel(
            GachaRatioLineupModel model,
            Action<GachaRatioResourceModel> onClickIcon)
        {
            return new GachaRatioLineupViewModel(
                model.RatioProbabilityAmount,
                model.GashaRatioLineupCellModels
                    .Select(cell => new GachaRatioLineupCellViewModel(
                        cell.ResourceModel,
                        PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(cell.PlayerResourceModel),
                        cell.CharacterName,
                        cell.ResourceName,
                        cell.OutputRatio,
                        cell.NumberParity,
                        onClickIcon))
                    .ToList());
        }
    }
}
