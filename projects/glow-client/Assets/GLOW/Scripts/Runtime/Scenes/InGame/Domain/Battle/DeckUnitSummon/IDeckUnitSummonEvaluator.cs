using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IDeckUnitSummonEvaluator
    {
        bool CanSummon(DeckUnitModel deckUnit, BattlePointModel battlePointModel);
    }
}
