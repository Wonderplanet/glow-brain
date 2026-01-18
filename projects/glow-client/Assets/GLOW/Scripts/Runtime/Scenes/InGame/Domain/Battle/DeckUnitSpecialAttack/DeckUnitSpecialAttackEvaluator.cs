using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class DeckUnitSpecialAttackEvaluator : IDeckUnitSpecialAttackEvaluator
    {
        public bool CanUseSpecialAttack(DeckUnitModel deckUnit)
        {
            if (deckUnit.IsEmptyUnit()) return false;
            if (deckUnit.RoleType == CharacterUnitRoleType.Special) return false;
            if (!deckUnit.IsSummoned) return false;
            if (deckUnit.IsSpecialAttackReady) return false;
            if (!deckUnit.RemainingSpecialAttackCoolTime.IsZero()) return false;

            return true;
        }
    }
}
