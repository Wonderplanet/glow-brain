using System.Linq;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.BoxGachaLineupDialog.Domain.Model;
using GLOW.Scenes.BoxGachaLineupDialog.Presentation.ViewModel;

namespace GLOW.Scenes.BoxGachaLineupDialog.Presentation.Translator
{
    public static class BoxGachaLineupModelTranslator
    {
        public static BoxGachaLineupDialogViewModel ToBoxGachaLineupDialogViewModel(BoxGachaLineupModel model)
        {
            return new BoxGachaLineupDialogViewModel(
                model.CurrentBoxLevel,
                ToBoxGachaLineupListViewModel(model.BoxGachaURLineupListViewModel),
                ToBoxGachaLineupListViewModel(model.BoxGachaSSRLineupListViewModel),
                ToBoxGachaLineupListViewModel(model.BoxGachaSRLineupListViewModel),
                ToBoxGachaLineupListViewModel(model.BoxGachaRLineupListViewModel),
                model.IsUnitContainInLineup);
        }
        
        static BoxGachaLineupListViewModel ToBoxGachaLineupListViewModel(BoxGachaLineupListModel model)
        {
            var cellViewModels = model.LineupCellModels
                .Select(ToBoxGachaLineupCellViewModel)
                .ToList();
            
            return new BoxGachaLineupListViewModel(
                model.Rarity,
                cellViewModels);
        }
        
        static BoxGachaLineupCellViewModel ToBoxGachaLineupCellViewModel(BoxGachaLineupCellModel model)
        {
            var resourceViewModel = PlayerResourceIconViewModelTranslator
                .ToPlayerResourceIconViewModel(model.PrizeIconModel);

            return new BoxGachaLineupCellViewModel(
                resourceViewModel,
                model.PrizeIconModel.Name,
                model.PrizeStock);
        }
    }
}