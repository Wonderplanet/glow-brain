using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.Mission.Presentation.ViewModel.BonusPointMission;

namespace GLOW.Scenes.BeginnerMission.Presentation.ViewModel
{
    public class BeginnerMissionMainViewModel : IBeginnerMissionMainViewModel
    {
        public BeginnerMissionDaysFromStart CurrentDaysFromStart { get; }
        public IBonusPointMissionViewModel BonusPointMissionViewModel { get; }
        public IReadOnlyDictionary<BeginnerMissionDayNumber, List<IBeginnerMissionCellViewModel>> BeginnerMissionCellViewModelsDictionary { get; }

        public BeginnerMissionMainViewModel(
            BeginnerMissionDaysFromStart currentDaysFromStart, 
            IBonusPointMissionViewModel bonusPointMissionViewModel, 
            IReadOnlyDictionary<BeginnerMissionDayNumber, List<IBeginnerMissionCellViewModel>> 
                beginnerMissionCellViewModelsDictionary)
        {
            CurrentDaysFromStart = currentDaysFromStart;
            BonusPointMissionViewModel = bonusPointMissionViewModel;
            BeginnerMissionCellViewModelsDictionary = beginnerMissionCellViewModelsDictionary;
        }
        
        public bool IsReceivableRewardExistFromDay(BeginnerMissionDayNumber dayNumber)
        {
            BeginnerMissionCellViewModelsDictionary.TryGetValue(dayNumber, out var beginnerMissionCellViewModels);
            return beginnerMissionCellViewModels?.Exists(cell => cell.MissionStatus == MissionStatus.Receivable) ?? false;
        }

        public bool IsReceivableRewardExist()
        {
            // NOTE: 獲得報酬があってかつ一番日の数字が小さいタブを初期タブとする。タブ内に獲得報酬がない場合は一番新しい日を初期タブとする
            var unlockedCellViewModelsDictionary = BeginnerMissionCellViewModelsDictionary
                .Where(x => x.Key <= CurrentDaysFromStart);
            
            var isExistReceivableDayNumbers = unlockedCellViewModelsDictionary
                .Any(x => x.Value.Any(y => y.MissionStatus == MissionStatus.Receivable));

            var isExistReceivableBonusPointMission = !BonusPointMissionViewModel.UnreceivedMissionRewardCount.IsZero();
            
            return isExistReceivableDayNumbers || isExistReceivableBonusPointMission;
        }
    }
}