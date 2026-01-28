using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction
{
    public interface ICharacterUnitAction
    {
        UnitActionState ActionState { get; }
        DamageInvalidationFlag IsDamageInvalidation { get; }
        HealInvalidationFlag IsHealInvalidation { get; }
        StateEffectInvalidationFlag IsAttackStateEffectInvalidation { get; }
        StateEffectInvalidationFlag IsNonAttackStateEffectInvalidation { get; }

        (CharacterUnitModel, IReadOnlyList<IAttackModel>) Update(CharacterUnitActionContext context);
        bool CanForceChangeTo(UnitActionState actionState);
    }
}
