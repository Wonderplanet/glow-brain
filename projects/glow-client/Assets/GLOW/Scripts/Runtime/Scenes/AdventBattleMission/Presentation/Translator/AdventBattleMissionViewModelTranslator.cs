using System.Linq;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.AdventBattleMission.Domain.Model;
using GLOW.Scenes.AdventBattleMission.Presentation.Component;
using GLOW.Scenes.AdventBattleMission.Presentation.ViewModel;

namespace GLOW.Scenes.AdventBattleMission.Presentation.Translator
{
    public class AdventBattleMissionViewModelTranslator
    {
        public static AdventBattleMissionViewModel ToAdventBattleMissionCellViewModels(
            AdventBattleMissionFetchResultModel model)
        {
            var adventBattleMissionCellViewModels = model.AdventBattleMissionCellModels
                .Select(ToAdventBattleMissionCellViewModel).ToList();
            
            var isBulkReceivable = adventBattleMissionCellViewModels
                    .Any(cell => cell.MissionStatus == MissionStatus.Receivable);
            var bulkReceivableFlag = new MissionBulkReceivableFlag(isBulkReceivable);
            
            return new AdventBattleMissionViewModel(
                adventBattleMissionCellViewModels,
                bulkReceivableFlag);
        }
        
        static AdventBattleMissionCellViewModel ToAdventBattleMissionCellViewModel(AdventBattleMissionCellModel model)
        {
            var resourceIconViewModels = PlayerResourceIconViewModelTranslator
                .ToPlayerResourceIconViewModels(model.PlayerResourceModels);
            
            return new AdventBattleMissionCellViewModel(
                model.AdventBattleMissionId,
                model.MissionType,
                model.MissionStatus,
                model.MissionProgress,
                model.CriterionCount,
                resourceIconViewModels,
                model.MissionDescription,
                model.DestinationScene,
                model.EndTime);
        }
    }
}