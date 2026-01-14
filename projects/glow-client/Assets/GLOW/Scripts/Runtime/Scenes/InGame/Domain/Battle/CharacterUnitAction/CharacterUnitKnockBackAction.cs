using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction
{
    public class CharacterUnitKnockBackAction : CharacterUnitKnockBackActionBase, ICharacterUnitAction
    {
        public UnitActionState ActionState => UnitActionState.KnockBack;
        public DamageInvalidationFlag IsDamageInvalidation => DamageInvalidationFlag.True;
        public HealInvalidationFlag IsHealInvalidation => HealInvalidationFlag.True;
        public StateEffectInvalidationFlag IsAttackStateEffectInvalidation => StateEffectInvalidationFlag.True;
        public StateEffectInvalidationFlag IsNonAttackStateEffectInvalidation => StateEffectInvalidationFlag.False;

        public bool CanForceChangeTo(UnitActionState actionState) => 
            actionState == UnitActionState.Restart ||
            actionState == UnitActionState.InterruptSlide ||
            actionState == UnitActionState.TransformationReady ||
            actionState == UnitActionState.Transformation;

        public CharacterUnitKnockBackAction(TickCount duration, float distance, ICharacterUnitAction prevAction)
            : base(duration, distance, prevAction)
        {
        }

        protected override ICharacterUnitAction CreateKnockBackAction(
            TickCount remainingDuration,
            float remainingDistance,
            ICharacterUnitAction prevAction)
        {
            return new CharacterUnitKnockBackAction(remainingDuration, remainingDistance, prevAction);
        }
    }
}
