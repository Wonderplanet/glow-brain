using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.InGame.Domain.Battle.Calculator
{
    public interface IUnitSpecialAttackCoolTimeCalculator
    {
        TickCount Calculate(
            TickCount mstCharacterCoolTime,
            TickCount specialRuleSpecialAttackCoolTimeParameter);
    }
}
