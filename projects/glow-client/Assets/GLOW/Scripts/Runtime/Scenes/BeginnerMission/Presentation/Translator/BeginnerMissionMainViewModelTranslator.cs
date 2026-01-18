using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.BeginnerMission.Domain.Model;
using GLOW.Scenes.BeginnerMission.Presentation.ViewModel;
using GLOW.Scenes.Mission.Presentation.Translator;

namespace GLOW.Scenes.BeginnerMission.Presentation.Translator
{
    public class BeginnerMissionMainViewModelTranslator
    {
        public static IBeginnerMissionMainViewModel ToBeginnerMissionMainViewModel(
            MissionBeginnerResultModel missionBeginnerResultModel, 
            BeginnerMissionDaysFromStart daysFromStart)
        {
            var bonusPointMissionViewModel = BonusPointMissionViewModelTranslator.ToBonusPointMissionViewModel(
                missionBeginnerResultModel.BonusPointResultModel, RemainingTimeSpan.Empty);
            var dayList = missionBeginnerResultModel.MissionBeginnerModel.Select(cell => cell.BeginnerMissionDayNumber)
                .Distinct().ToList();
            var beginnerMissionCellViewModelsDictionary = dayList.ToDictionary(
                day => day,
                day => missionBeginnerResultModel.MissionBeginnerModel
                    .Where(cell => cell.BeginnerMissionDayNumber == day)
                    .Select(cell => BeginnerMissionCellViewModelTranslator.ToBeginnerMissionCellViewModel(
                        cell, 
                        daysFromStart))
                    .ToList());
            
            return new BeginnerMissionMainViewModel(
                daysFromStart, 
                bonusPointMissionViewModel, 
                beginnerMissionCellViewModelsDictionary);
        }
    }
}