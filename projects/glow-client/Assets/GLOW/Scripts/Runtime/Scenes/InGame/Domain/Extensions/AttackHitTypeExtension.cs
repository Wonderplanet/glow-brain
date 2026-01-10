using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using UnityEngine;

namespace GLOW.Scenes.InGame.Domain.Extensions
{
    public static class AttackHitTypeExtension
    {
        public static bool IsKnockBack(this AttackHitType attackHitType)
        {
            return attackHitType switch
            {
                AttackHitType.AccumulatedDamageKnockBack => true,
                AttackHitType.KnockBack1 => true,
                AttackHitType.KnockBack2 => true,
                AttackHitType.KnockBack3 => true,
                AttackHitType.ForcedKnockBack1 => true,
                AttackHitType.ForcedKnockBack2 => true,
                AttackHitType.ForcedKnockBack3 => true,
                AttackHitType.ForcedKnockBack5 => true,
                _ => false
            };
        }

        /// <summary>
        /// ノックバックの次に実行するUnitActionとして予約できるか
        /// </summary>
        public static bool CanReserveActionAfterKnockBack(this AttackHitType attackHitType)
        {
            return attackHitType switch
            {
                AttackHitType.Stun => true,
                AttackHitType.Freeze => true,
                _ => false
            };
        }

        public static StateEffectType GetStateEffectTypeThatBlockMe(this AttackHitType attackHitType)
        {
            return attackHitType switch
            {
                AttackHitType.KnockBack1 => StateEffectType.KnockBackBlock,
                AttackHitType.KnockBack2 => StateEffectType.KnockBackBlock,
                AttackHitType.KnockBack3 => StateEffectType.KnockBackBlock,
                AttackHitType.ForcedKnockBack1 => StateEffectType.ForcedKnockBackBlock,
                AttackHitType.ForcedKnockBack2 => StateEffectType.ForcedKnockBackBlock,
                AttackHitType.ForcedKnockBack3 => StateEffectType.ForcedKnockBackBlock,
                AttackHitType.ForcedKnockBack5 => StateEffectType.ForcedKnockBackBlock,
                AttackHitType.Stun => StateEffectType.StunBlock,
                AttackHitType.Freeze => StateEffectType.FreezeBlock,
                _ => StateEffectType.None
            };
        }

        public static int GetActionPriority(this AttackHitType attackHitType)
        {
            return attackHitType switch
            {
                AttackHitType.Stun => 1,
                AttackHitType.Freeze => 2,
                AttackHitType.AccumulatedDamageKnockBack => 3,
                AttackHitType.KnockBack1 => 4,
                AttackHitType.KnockBack2 => 5,
                AttackHitType.KnockBack3 => 6,
                AttackHitType.ForcedKnockBack1 => 7,
                AttackHitType.ForcedKnockBack2 => 8,
                AttackHitType.ForcedKnockBack3 => 9,
                AttackHitType.ForcedKnockBack5 => 10,
                _ => 0
            };
        }

        public static UnitActionState GetUnitActionState(this AttackHitType attackHitType)
        {
            return attackHitType switch
            {
                AttackHitType.AccumulatedDamageKnockBack => UnitActionState.KnockBack,
                AttackHitType.KnockBack1 => UnitActionState.KnockBack,
                AttackHitType.KnockBack2 => UnitActionState.KnockBack,
                AttackHitType.KnockBack3 => UnitActionState.KnockBack,
                AttackHitType.ForcedKnockBack1 => UnitActionState.ForceKnockBack,
                AttackHitType.ForcedKnockBack2 => UnitActionState.ForceKnockBack,
                AttackHitType.ForcedKnockBack3 => UnitActionState.ForceKnockBack,
                AttackHitType.ForcedKnockBack5 => UnitActionState.ForceKnockBack,
                AttackHitType.Stun => UnitActionState.Stun,
                AttackHitType.Freeze => UnitActionState.Freeze,
                _ => UnitActionState.None
            };
        }
    }
}
