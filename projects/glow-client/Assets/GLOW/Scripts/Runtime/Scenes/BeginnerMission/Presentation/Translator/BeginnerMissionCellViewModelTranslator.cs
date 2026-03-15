using System.Linq;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.BeginnerMission.Domain.Model;
using GLOW.Scenes.BeginnerMission.Domain.ValueObject;
using GLOW.Scenes.BeginnerMission.Presentation.ViewModel;

namespace GLOW.Scenes.BeginnerMission.Presentation.Translator
{
    public class BeginnerMissionCellViewModelTranslator
    {
        public static IBeginnerMissionCellViewModel ToBeginnerMissionCellViewModel(
            MissionBeginnerCellModel cellModel, 
            BeginnerMissionDaysFromStart daysFromStart)
        {
            return new BeginnerMissionCellViewModel(
                cellModel.MissionBeginnerId,
                cellModel.MissionStatus,
                cellModel.MissionProgress,
                cellModel.CriterionValue,
                cellModel.CriterionCount,
                cellModel.PlayerResourceModels
                    .Select(model => PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(model))
                    .ToList(),
                cellModel.BonusPoint,
                new BeginnerMissionLockFlag(cellModel.BeginnerMissionDayNumber > daysFromStart),
                cellModel.MissionDescription,
                cellModel.DestinationScene);
        }
    }
}