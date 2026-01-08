using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.Calculator
{
    public interface IUnitSummonCoolTimeCalculator
    {
        TickCount Calculate(MstCharacterModel mstCharacter, OutpostEnhancementModel enhancement);
    }
}

