using GLOW.Scenes.BoxGachaConfirm.Domain.Model;
using GLOW.Scenes.BoxGachaConfirm.Presentation.ViewModel;

namespace GLOW.Scenes.BoxGachaConfirm.Presentation.Translator
{
    public static class BoxGachaConfirmDialogViewModelTranslator
    {
        public static BoxGachaConfirmDialogViewModel ToViewModel(BoxGachaConfirmDialogModel model)
        {
            return new BoxGachaConfirmDialogViewModel(
                model.CostItemName,
                model.CostItemIconAssetPath,
                model.OfferCostItemAmount,
                model.CostItemAmount,
                model.BoxGachaName,
                model.CanSelectDrawCount,
                model.IsDrawable);
        }
    }
}