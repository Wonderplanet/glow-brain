using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.ItemDetail.Domain.Models;
using GLOW.Scenes.ItemDetail.Presentation.ViewModels;
using GLOW.Scenes.ItemDetail.Presentation.Views;

namespace GLOW.Scenes.ItemDetail.Presentation.Translator
{
    public class ItemDetailWithTransitViewModelTranslator
    {
        public static ItemDetailWithTransitViewModel ToItemDetailWithTransitViewModel(ItemDetailWithTransitModel model)
        {
            return new ItemDetailWithTransitViewModel(
                PlayerResourceIconViewModelTranslator.ToPlayerResourceDetailViewModel(model.PlayerResourceModel),
                ToItemDetailAmountViewModel(model.ItemDetailAmountModel),
                ToItemDetailAvailableLocationViewModel(model.ItemDetailAvailableLocationModel));
        }

        public static ItemDetailAvailableLocationViewModel ToItemDetailAvailableLocationViewModel(ItemDetailAvailableLocationModel model)
        {
            return new ItemDetailAvailableLocationViewModel(
                ToItemDetailEarnLocationViewModel(model.EarnLocationModel1),
                ToItemDetailEarnLocationViewModel(model.EarnLocationModel2)
            );
        }

        static ItemDetailAmountViewModel ToItemDetailAmountViewModel(ItemDetailAmountModel model)
        {
            return new ItemDetailAmountViewModel(
                model.CurrentAmount,
                model.PaidDiamondAmount
            );
        }
        static ItemDetailEarnLocationViewModel ToItemDetailEarnLocationViewModel(ItemDetailEarnLocationModel model)
        {
            return new ItemDetailEarnLocationViewModel(
                model.TransitionType,
                model.MasterDataId,
                model.TransitionPossibleFlag
            );
        }    }
}
