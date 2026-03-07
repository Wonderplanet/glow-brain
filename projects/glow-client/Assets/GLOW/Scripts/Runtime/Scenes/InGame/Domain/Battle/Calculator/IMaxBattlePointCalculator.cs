using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.Calculator
{
    public interface IMaxBattlePointCalculator
    {
        BattlePoint Calculate(OutpostEnhancementModel enhancement);
    }
}
