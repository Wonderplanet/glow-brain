using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine.Profiling;

namespace GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction
{
    /// <summary>
    /// 必殺ワザ直前のAction
    /// 1フレーム固定
    /// このタイミングでカットイン開始する
    /// </summary>
    public class CharacterUnitPreSpecialAttackAction : ICharacterUnitAction
    {
        public UnitActionState ActionState => UnitActionState.PreSpecialAttack;
        public DamageInvalidationFlag IsDamageInvalidation => DamageInvalidationFlag.False;
        public HealInvalidationFlag IsHealInvalidation => HealInvalidationFlag.False;
        public StateEffectInvalidationFlag IsAttackStateEffectInvalidation => StateEffectInvalidationFlag.False;
        public StateEffectInvalidationFlag IsNonAttackStateEffectInvalidation => StateEffectInvalidationFlag.False;

        public bool CanForceChangeTo(UnitActionState actionState) => actionState != UnitActionState.KnockBack;

        public (CharacterUnitModel, IReadOnlyList<IAttackModel>) Update(CharacterUnitActionContext context)
        {
            Profiler.BeginSample("CharacterUnitPreSpecialAttackAction.Update");
            var unit = context.CharacterUnit;

            var nextAction = new CharacterUnitSpecialAttackAction(TickCount.Zero);

            var updatedUnit =  context.CharacterUnit with
            {
                Action = nextAction,
                PrevActionState = unit.Action.ActionState,
                PrevLocatedKoma = unit.LocatedKoma,
            };

            Profiler.EndSample();
            return (updatedUnit, new List<IAttackModel>());
        }
    }
}
