using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.Calculator
{
    public interface IRushChargeTimeCalculator
    {
        TickCount Calculate(OutpostEnhancementModel outpostEnhancement, IMstConfigRepository mstConfigRepository);
    }
}

