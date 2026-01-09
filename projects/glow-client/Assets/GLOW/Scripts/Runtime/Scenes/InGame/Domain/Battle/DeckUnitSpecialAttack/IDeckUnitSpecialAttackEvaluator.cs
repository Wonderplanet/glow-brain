using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IDeckUnitSpecialAttackEvaluator
    {
        bool CanUseSpecialAttack(DeckUnitModel deckUnit);
    }
}
