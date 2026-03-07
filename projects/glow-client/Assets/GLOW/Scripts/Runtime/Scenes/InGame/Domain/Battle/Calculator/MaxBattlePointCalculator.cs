using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.Calculator
{
    public class MaxBattlePointCalculator : IMaxBattlePointCalculator
    {
        [Inject] IMstConfigRepository MstConfigRepository { get; }

        public BattlePoint Calculate(OutpostEnhancementModel enhancement)
        {
            var config = MstConfigRepository.GetConfig(MstConfigKey.InGameMaxBattlePoint);
            var defaultMaxBattlePoint = config.Value.ToBattlePoint();
            
            var offset = enhancement.GetEnhancementValue(OutpostEnhancementType.LeaderPointLimit);
            
            return defaultMaxBattlePoint + offset.ToBattlePoint();
        }
    }
}
