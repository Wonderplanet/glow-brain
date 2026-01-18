using System.Linq;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.GachaHistoryDetailDialog.Presentation.ViewModels;
using GLOW.Scenes.GachaHistoryDialog.Domain.Models;
using GLOW.Scenes.GachaHistoryDialog.Presentation.ViewModels;

namespace GLOW.Scenes.GachaHistoryDialog.Presentation.Translator
{
    public class GachaHistoryDialogViewModelTranslator
    {
        public static GachaHistoryDialogViewModel Translate(GachaHistoryUseCaseModel useCaseModel)
        {
            return new GachaHistoryDialogViewModel(
                useCaseModel.GachaHistoryCellModels.Select(Translate).ToList(),
                useCaseModel.GachaHistoryDetailModels.Select(Translate).ToList());
        }
        
        static GachaHistoryCellViewModel Translate(GachaHistoryCellModel model)
        {
            return new GachaHistoryCellViewModel(
                model.GachaDrawDate,
                model.GachaName,
                model.AdDrawFlag,
                model.CostItemPlayerResourceIconAssetPath,
                model.CostAmount);
        }
        
        static GachaHistoryDetailDialogViewModel Translate(GachaHistoryDetailModel model)
        {
            return new GachaHistoryDetailDialogViewModel(
                model.CellModels.Select(Translate).ToList()
                );
        }
        
        static GachaHistoryDetailCellViewModel Translate(GachaHistoryDetailCellModel model)
        {
            PlayerResourceIconViewModel acquiredAmountPlayerResourceIconViewModel;
            if (model.AcquiredAmountPlayerResourceModel.IsEmpty())
            {
                acquiredAmountPlayerResourceIconViewModel = PlayerResourceIconViewModel.Empty;
            }
            else
            {
                acquiredAmountPlayerResourceIconViewModel = 
                    PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(
                        model.AcquiredAmountPlayerResourceModel);
            }
            
            
            return new GachaHistoryDetailCellViewModel(
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(model.CellIconPlayerResourceModel),
                model.CellIconPlayerResourceModel.Name,
                model.CharacterName,
                acquiredAmountPlayerResourceIconViewModel.AssetPath,
                acquiredAmountPlayerResourceIconViewModel.Amount);
                
        }
    }
}