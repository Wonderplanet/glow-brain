using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.Calculator
{
    public class BattlePointChargeAmountCalculator : IBattlePointChargeAmountCalculator
    {
        [Inject] IMstConfigRepository _mstConfigRepository { get; }

        public BattlePoint Calculate(OutpostEnhancementModel enhancement)
        {
            var config = _mstConfigRepository.GetConfig(MstConfigKey.InGameBattlePointChargeAmount);
            var defaultChargeAmount = config.Value.ToBattlePoint();
            
            var offset = enhancement.GetEnhancementValue(OutpostEnhancementType.LeaderPointSpeed);
            
            return defaultChargeAmount + offset.ToBattlePoint();
        }
    }
}
