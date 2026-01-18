using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EnhanceQuestTop.Domain.Models;
using Zenject;

namespace GLOW.Scenes.EnhanceQuestTop.Domain.Factories
{
    public class QuestPartyModelFactory : IQuestPartyModelFactory
    {
        [Inject] IMstQuestBonusUnitRepository MstQuestBonusUnitRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public QuestPartyModel Create(UserPartyCacheModel party, MasterDataId mstQuestId)
        {
            var partyName = party.PartyName;

            var mstBonusUnits = MstQuestBonusUnitRepository
                .GetQuestBonusUnits(mstQuestId)
                .Where(mst => CalculateTimeCalculator.IsValidTime(TimeProvider.Now, mst.StartAt, mst.EndAt))
                .ToList();
            
            var userUnits = GameRepository.GetGameFetchOther().UserUnitModels;
            
            var totalBonusPercentage = party.GetUnitList()
                .Where(userUnitId => !userUnitId.IsEmpty())
                .Join(
                    userUnits,
                    userUnitId => userUnitId,
                    userUnit => userUnit.UsrUnitId,
                    (userUnitId, userUnit) => userUnit)
                .Join(
                    mstBonusUnits,
                    userUnit => userUnit.MstUnitId,
                    mstBonus => mstBonus.MstUnitId,
                    (userUnit, mstBonus) => mstBonus)
                .Select(mstBonus => mstBonus.CoinBonusRate.ToEventBonusPercentage())
                .Aggregate(0, (n, next) => n + next.Value);

            return new QuestPartyModel(partyName, new EventBonusPercentage(totalBonusPercentage));
        }
    }
}
