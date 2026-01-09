using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class InGameEventBonusUnitEffectProvider : IInGameEventBonusUnitEffectProvider
    {
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstEventBonusUnitDataRepository MstEventBonusUnitDataRepository { get; }
        [Inject] IMstQuestEventBonusScheduleDataRepository MstQuestEventBonusScheduleDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public EventBonusPercentage GetUnitEventBonus(MasterDataId mstUnitId, MasterDataId mstQuestId)
        {
            if(mstQuestId.IsEmpty()) return EventBonusPercentage.Empty;

            // 強化クエストの場合ボーナスがスコアにかかる
            var mstQuest = MstQuestDataRepository.GetMstQuestModel(mstQuestId);
            if(mstQuest.QuestType == QuestType.Enhance) return EventBonusPercentage.Empty;

            var now = TimeProvider.Now;
            var bonusSchedule = MstQuestEventBonusScheduleDataRepository.GetQuestEventBonusSchedules(mstQuestId)
                .Where(mst => CalculateTimeCalculator.IsValidTime(now, mst.StartAt, mst.EndAt))
                .DefaultIfEmpty(MstQuestEventBonusScheduleModel.Empty)
                .OrderByDescending(mst => mst.StartAt)
                .First();

            // 有効なスケジュール設定のない場合はボーナスなし
            if(bonusSchedule.IsEmpty()) return EventBonusPercentage.Empty;

            var bonusUnits = MstEventBonusUnitDataRepository.GetEventBonuses(bonusSchedule.EventBonusGroupId);
            var bonusUnit = bonusUnits.FirstOrDefault(mst => mst.MstUnitId == mstUnitId);
            return bonusUnit?.BonusPercentage ?? EventBonusPercentage.Empty;
        }

        public EventBonusPercentage GetUnitEventBonus(MasterDataId mstUnitId, EventBonusGroupId eventBonusGroupId)
        {
            if (eventBonusGroupId.IsEmpty()) return EventBonusPercentage.Empty;

            var bonusUnits = MstEventBonusUnitDataRepository.GetEventBonuses(eventBonusGroupId);
            var bonusUnit = bonusUnits.FirstOrDefault(mst => mst.MstUnitId == mstUnitId);
            return bonusUnit?.BonusPercentage ?? EventBonusPercentage.Empty;
        }

        public PercentageM GetUnitEventBonusPercentageM(MasterDataId mstUnitId, MasterDataId mstQuestId)
        {
            var eventBonus = GetUnitEventBonus(mstUnitId, mstQuestId);
            if(eventBonus.IsEmpty()) return PercentageM.Hundred;

            return PercentageM.Hundred + eventBonus.ToPercentageM();
        }

        public PercentageM GetUnitEventBonusPercentageM(MasterDataId mstUnitId, EventBonusGroupId eventBonusGroupId)
        {
            var eventBonus = GetUnitEventBonus(mstUnitId, eventBonusGroupId);
            if(eventBonus.IsEmpty()) return PercentageM.Hundred;

            return PercentageM.Hundred + eventBonus.ToPercentageM();
        }
    }
}
