using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.Calculator
{
    public class UnitSummonCoolTimeCalculator : IUnitSummonCoolTimeCalculator
    {
        const int MinSummonCoolTime = 100;

        TickCount IUnitSummonCoolTimeCalculator.Calculate(
            MstCharacterModel mstCharacter,
            OutpostEnhancementModel enhancement,
            TickCount specialRuleSummonCoolTimeParameter)
        {
            var offset = enhancement.GetEnhancementValue(OutpostEnhancementType.SummonInterval);
            var summonCoolTime =
                mstCharacter.SummonCoolTime
                - offset.ToTickCount()
                - specialRuleSummonCoolTimeParameter;

            // 100フレーム(2秒)以上は速くできない
            if (summonCoolTime.Value < MinSummonCoolTime && mstCharacter.SummonCoolTime.Value > MinSummonCoolTime)
            {
                summonCoolTime = new TickCount(MinSummonCoolTime);
            }

            return summonCoolTime;
        }
    }
}
