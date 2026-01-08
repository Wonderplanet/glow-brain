using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Models.StateEffectConditionModels
{
    public record StateEffectAttackerOrEmptyConditionModel(
        IReadOnlyList<CharacterUnitRoleType> AttackerRoleTypes,
        IReadOnlyList<CharacterColor> AttackerColors) : IStateEffectConditionModel
    {
        public bool MeetsCondition(IStateEffectConditionContext context)
        {
            // 無条件の場合(コマからの付与)
            if (context is StateEffectEmptyConditionContext)
            {
                return true;
            }

            // 攻撃からの付与の場合は攻撃者条件をチェック
            if (context is StateEffectAttackHitConditionContext attackHitConditionContext)
            {
                return AttackerRoleTypes.Contains(attackHitConditionContext.AttackerRoleType) ||
                       AttackerColors.Contains(attackHitConditionContext.AttackerColor);
            }

            return false;
        }
    }
}

