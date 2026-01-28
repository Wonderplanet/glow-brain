using System;
using System.Linq;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.GachaRatio.Domain.Model;
using GLOW.Scenes.GachaRatio.Presentation.ViewModels;

namespace GLOW.Scenes.GachaRatio.Presentation.Translator
{
    public class GachaRatioDialogViewModelTranslator
    {
        public static GachaRatioDialogViewModel TranslateToViewModel(
            GachaRatioDialogUseCaseModel model,
            Action<GachaRatioResourceModel> onClickIcon)
        {
            return new GachaRatioDialogViewModel(
                ToRatioPageViewModel(model.NormalPrizePageModel, onClickIcon),
                ToRatioPageViewModel(model.NormalPrizeInFixedPageModel, onClickIcon),
                ToRatioPageViewModel(model.UpperPrizeInMaxRarityPageModel, onClickIcon),
                ToRatioPageViewModel(model.UpperPrizeInPickupPageModel, onClickIcon),
                model.GachaFixedPrizeDescription
            );
        }

        static GachaRatioPageViewModel ToRatioPageViewModel(GachaRatioPageModel model, Action<GachaRatioResourceModel> onClickIcon)
        {
            if (model.IsEmpty()) return GachaRatioPageViewModel.Empty;

            return new GachaRatioPageViewModel(
                ToRarityRatioViewModel(model.RarityRatioModel),
                ToRatioLineupListViewModel(model.GachaRatioPickupListModel, onClickIcon),
                ToRatioLineupListViewModel(model.GachaRatioLineupListModel, onClickIcon)
            );
        }

        static GachaRatioByRarityViewModel ToRarityRatioViewModel(GachaRatioRarityRatioModel model)
        {
            return new GachaRatioByRarityViewModel(
                new GachaRatioRarityRatioItemViewModel(model.UR.Rarity,model.UR.Probability),
                new GachaRatioRarityRatioItemViewModel(model.SSR.Rarity,model.SSR.Probability),
                new GachaRatioRarityRatioItemViewModel(model.SR.Rarity,model.SR.Probability),
                new GachaRatioRarityRatioItemViewModel(model.R.Rarity,model.R.Probability)
                );
        }

        static GachaRatioLineupListViewModel ToRatioLineupListViewModel(GachaRatioLineupListModel model,
            Action<GachaRatioResourceModel> onClickIcon)
        {
            return new GachaRatioLineupListViewModel(
                ToRatioLineupViewModel(model.URareLineupModel, onClickIcon),
                ToRatioLineupViewModel(model.SSRareLineupModel, onClickIcon),
                ToRatioLineupViewModel(model.SRareLineupModel, onClickIcon),
                ToRatioLineupViewModel(model.RLineupModel, onClickIcon)
            );
        }

        static GachaRatioLineupViewModel ToRatioLineupViewModel(GachaRatioLineupModel model, Action<GachaRatioResourceModel> onClickIcon)
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
                        onClickIcon
                    ))
                    .ToList()
            );
        }
    }
}