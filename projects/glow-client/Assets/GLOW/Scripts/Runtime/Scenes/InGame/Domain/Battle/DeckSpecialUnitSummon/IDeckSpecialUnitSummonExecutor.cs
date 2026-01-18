using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IDeckSpecialUnitSummonExecutor
    {
        DeckSpecialUnitSummonResult Summon(DeckUnitModel deckUnit, BattlePointModel battlePointModel);
    }
}