using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Models.StateEffectConditionModels
{
    public record StateEffectAttackHitConditionContext(
        CharacterColor AttackerColor,
        CharacterUnitRoleType AttackerRoleType) : IStateEffectConditionContext;
}