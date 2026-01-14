using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Extensions
{
    public static class AttackActionStateExtension
    {
        public static bool IsKnockBackState(this UnitActionState unitActionState)
        {
            return unitActionState switch
            {
                UnitActionState.KnockBack => true,
                UnitActionState.ForceKnockBack => true,
                _ => false
            };
        }
    }
}