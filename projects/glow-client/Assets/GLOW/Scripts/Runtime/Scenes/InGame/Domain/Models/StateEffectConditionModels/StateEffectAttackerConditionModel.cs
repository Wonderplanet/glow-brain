using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Models.StateEffectConditionModels
{
    public record StateEffectAttackerConditionModel(
        IReadOnlyList<CharacterUnitRoleType> AttackerRoleTypes,
        IReadOnlyList<CharacterColor> AttackerColors) : IStateEffectConditionModel
    {
        public bool MeetsCondition(IStateEffectConditionContext context)
        {
            if (context is StateEffectAttackHitConditionContext attackHitConditionContext)
            {
                return AttackerRoleTypes.Contains(attackHitConditionContext.AttackerRoleType) ||
                       AttackerColors.Contains(attackHitConditionContext.AttackerColor);
            }
            
            return false;
        }
    }
}