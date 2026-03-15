using System.Linq;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Translators;
using GLOW.Core.Domain.ValueObjects;
using WonderPlanet.CultureSupporter.Time;
using Zenject;

namespace GLOW.Scenes.EnhanceQuestTop.Domain.UseCases
{
    public class SetPartyFormationEventBonusUseCase
    {
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IMstEventBonusUnitDataRepository MstQuestEventBonusDataRepository { get; }
        [Inject] IMstQuestBonusUnitRepository MstQuestBonusUnitRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public void SetEventBonus(EventBonusGroupId eventBonusGroupId)
        {
            var bonusUnits = MstQuestEventBonusDataRepository.GetEventBonuses(eventBonusGroupId)
                .Select(PartyBonusUnitTranslator.Translate)
                .ToList();
            PartyCacheRepository.SetBonusUnits(bonusUnits);
        }

        public void SetEnhanceQuestBonus(MasterDataId mstQuestId)
        {
            var bonusUnits = MstQuestBonusUnitRepository.GetQuestBonusUnits(mstQuestId)
                .Where(mst => CalculateTimeCalculator.IsValidTime(TimeProvider.Now, mst.StartAt, mst.EndAt))
                .Select(PartyBonusUnitTranslator.Translate)
                .ToList();
            PartyCacheRepository.SetBonusUnits(bonusUnits);
        }
    }
}
