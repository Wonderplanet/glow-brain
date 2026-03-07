using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.InGame.Domain.Battle.Calculator
{
    public class UnitSpecialAttackCoolTimeCalculator : IUnitSpecialAttackCoolTimeCalculator
    {
        const int MinCoolTime = 50;

        TickCount IUnitSpecialAttackCoolTimeCalculator.Calculate(
            TickCount mstCharacterCoolTime,
            TickCount specialRuleSpecialAttackCoolTimeParameter,
            TickCount artworkEffectSpecialAttackCoolTime)
        {
            var specialAttackCoolTime = mstCharacterCoolTime
                                        - specialRuleSpecialAttackCoolTimeParameter
                                        - artworkEffectSpecialAttackCoolTime;
            if (specialAttackCoolTime.Value < MinCoolTime)
            {
                specialAttackCoolTime = new TickCount(MinCoolTime);
            }

            return specialAttackCoolTime;
        }
    }
}
