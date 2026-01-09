using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IDeckUnitSummonExecutor
    {
        DeckUnitSummonResult Summon(DeckUnitModel deckUnit, BattlePointModel battlePointModel);
    }
}
