using System.Linq;
using GLOW.Scenes.ArtworkFragment.Presentation.Translator;
using GLOW.Scenes.OutpostEnhance.Domain.Models;
using GLOW.Scenes.OutpostEnhance.Presentation.ViewModels;

namespace GLOW.Scenes.OutpostEnhance.Presentation.Translator
{
    public class OutpostEnhanceViewModelTranslator
    {
        public static OutpostEnhanceViewModel ToOutpostEnhanceViewModel(OutpostEnhanceUseCaseModel useCaseModel)
        {
            var buttons = useCaseModel.Buttons
                .Select(useCaseButtonModel =>
                    new OutpostEnhanceTypeButtonViewModel(
                        useCaseButtonModel.Id,
                        useCaseButtonModel.EnhanceId,
                        useCaseButtonModel.EnhanceLevelId,
                        useCaseButtonModel.Name,
                        useCaseButtonModel.Description,
                        useCaseButtonModel.Level,
                        useCaseButtonModel.MaxLevel,
                        useCaseButtonModel.Cost,
                        useCaseButtonModel.IconAssetPath,
                        useCaseButtonModel.MaxLevel.Value <= useCaseButtonModel.Level.Value,
                        useCaseButtonModel.Cost.Value > useCaseModel.UserCoin.Value
                    )).ToList();

            return new OutpostEnhanceViewModel(useCaseModel.OutpostHp, buttons);
        }

        public static OutpostEnhanceArtworkListViewModel TranslateArtworkListViewModel(OutpostEnhanceArtworkListModel model)
        {
            var cells = model.Cells
                .Select(cell => new OutpostEnhanceArtworkListCellViewModel(
                    cell.MstArtworkId,
                    ArtworkPanelViewModelTranslator.ToArtworkFragmentPanelViewModel(cell.ArtworkPanelModel),
                    cell.Badge,
                    cell.IsLock,
                    cell.IsSelect
                )).ToList();
            return new OutpostEnhanceArtworkListViewModel(cells);
        }
    }
}
