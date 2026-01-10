using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EventBonusUnitList.Domain.Models;
using Zenject;

namespace GLOW.Scenes.EventBonusUnitList.Domain.UseCases
{
    public class ShowEventBonusUnitListUseCase
    {
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstEventBonusUnitDataRepository MstEventBonusUnitDataRepository { get; }
        [Inject] IMstQuestBonusUnitRepository MstQuestBonusUnitRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public EventBonusUnitListModel GetBonus(EventBonusGroupId eventBonusGroupId, MasterDataId mstQuestId)
        {
            var bonusList = new List<EventBonusUnitModel>();
            if (!eventBonusGroupId.IsEmpty())
            {
                bonusList = MstEventBonusUnitDataRepository.GetEventBonuses(eventBonusGroupId)
                    .OrderByDescending(mst => mst.BonusPercentage)
                    .Select(Translate)
                    .ToList();
            }
            else if(!mstQuestId.IsEmpty())
            {
                bonusList = MstQuestBonusUnitRepository.GetQuestBonusUnits(mstQuestId)
                    .Where(mst => CalculateTimeCalculator.IsValidTime(TimeProvider.Now, mst.StartAt, mst.EndAt))
                    .OrderByDescending(mst => mst.CoinBonusRate)
                    .Select(Translate)
                    .ToList();
            }

            return new EventBonusUnitListModel(bonusList);
        }

        EventBonusUnitModel Translate(MstEventBonusUnitModel model)
        {
            var mstUnit = MstCharacterDataRepository.GetCharacter(model.MstUnitId);
            return new EventBonusUnitModel(
                CharacterIconModelFactory.Create(mstUnit),
                model.BonusPercentage);
        }

        EventBonusUnitModel Translate(MstQuestBonusUnitModel model)
        {
            var mstUnit = MstCharacterDataRepository.GetCharacter(model.MstUnitId);
            return new EventBonusUnitModel(
                CharacterIconModelFactory.Create(mstUnit),
                model.CoinBonusRate.ToEventBonusPercentage());
        }
    }
}
