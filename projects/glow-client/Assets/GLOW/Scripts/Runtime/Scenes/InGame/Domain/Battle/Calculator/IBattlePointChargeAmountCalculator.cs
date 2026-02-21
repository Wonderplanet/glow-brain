using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.Calculator
{
    public interface IBattlePointChargeAmountCalculator
    {
        BattlePoint Calculate(OutpostEnhancementModel enhancement);
    }
}
