using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.Calculator
{
    public class UnitSummonCoolTimeCalculator : IUnitSummonCoolTimeCalculator
    {
        const int MinSummonCoolTime = 100;
        
        public TickCount Calculate(MstCharacterModel mstCharacter, OutpostEnhancementModel enhancement)
        {
            var offset = enhancement.GetEnhancementValue(OutpostEnhancementType.SummonInterval);
            var summonCoolTime = mstCharacter.SummonCoolTime - offset.ToTickCount();
            
            // 100フレーム(2秒)以上は速くできない
            if (summonCoolTime.Value < MinSummonCoolTime && mstCharacter.SummonCoolTime.Value > MinSummonCoolTime)
            {
                summonCoolTime = new TickCount(MinSummonCoolTime);
            }
            
            return summonCoolTime;
        }
    }
}
