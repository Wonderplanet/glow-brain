using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.Mission.Presentation.ViewModel.BonusPointMission;

namespace GLOW.Scenes.BeginnerMission.Presentation.ViewModel
{
    public interface IBeginnerMissionMainViewModel
    {
        public BeginnerMissionDaysFromStart CurrentDaysFromStart { get; }
        public IBonusPointMissionViewModel BonusPointMissionViewModel { get; }
        public IReadOnlyDictionary<BeginnerMissionDayNumber, List<IBeginnerMissionCellViewModel>> BeginnerMissionCellViewModelsDictionary { get; }
        bool IsReceivableRewardExistFromDay(BeginnerMissionDayNumber dayNumber);
        bool IsReceivableRewardExist();
    }
}