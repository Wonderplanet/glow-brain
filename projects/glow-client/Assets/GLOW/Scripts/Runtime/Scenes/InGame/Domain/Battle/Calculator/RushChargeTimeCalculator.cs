using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.Calculator
{
    public class RushChargeTimeCalculator : IRushChargeTimeCalculator
    {
        public TickCount Calculate(
            OutpostEnhancementModel outpostEnhancement,
            IMstConfigRepository mstConfigRepository)
        {
            var defaultChargeTime = mstConfigRepository.GetConfig(MstConfigKey.RushDefaultChargeTime).Value.ToTickCount();
            var minChargeTime = mstConfigRepository.GetConfig(MstConfigKey.RushMinChargeTime).Value.ToTickCount();
            
            var rushChargeSpeedOffset = outpostEnhancement.GetEnhancementValue(OutpostEnhancementType.RushChargeSpeed);
            
            var chargeTime = defaultChargeTime - rushChargeSpeedOffset.ToTickCount();
            chargeTime = TickCount.Max(chargeTime, minChargeTime);
            
            return chargeTime;
        }
    }
}
