using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.EventQuestTop.Domain.UseCases
{
    public class OpenAdventBattleGetUseCase
    {
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public MstAdventBattleModel GetOpenAdventBattle(MasterDataId mstAdventBattleId)
        {
            if(mstAdventBattleId.IsEmpty()) return MstAdventBattleModel.Empty;
            var mstAdventBattle = MstAdventBattleDataRepository.GetMstAdventBattleModel(mstAdventBattleId);
            var isOpen = CalculateTimeCalculator.IsValidTime(TimeProvider.Now, mstAdventBattle.StartDateTime.Value, mstAdventBattle.EndDateTime.Value);

            return isOpen ? mstAdventBattle : MstAdventBattleModel.Empty;
        }
    }
}
