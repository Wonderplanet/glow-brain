using GLOW.Scenes.InGame.Presentation.Constants;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.ValueObjects
{
    public record CharacterUnitAnimation(
        UnitAnimationType Type,
        string Name,
        int AnimatorHash,
        UnitAnimationLoopFlag IsLoop,
        UnitAnimationHitStopFlag CanHitStop,
        UnitAnimationHoldAtEndFlag IsHoldAtEnd)
    {
        public static CharacterUnitAnimation Empty { get; } = new(
            UnitAnimationType.Empty,
            "",
            0,
            UnitAnimationLoopFlag.False,
            UnitAnimationHitStopFlag.False,
            UnitAnimationHoldAtEndFlag.False);

        public static CharacterUnitAnimation Wait { get; } = new(
            UnitAnimationType.Wait,
            "wait",
            Animator.StringToHash("wait"),
            UnitAnimationLoopFlag.True,
            UnitAnimationHitStopFlag.False,
            UnitAnimationHoldAtEndFlag.False);

        public static CharacterUnitAnimation MirrorWait { get; } = new(
            UnitAnimationType.Wait,
            "wait_mir",
            Animator.StringToHash("wait_mir"),
            UnitAnimationLoopFlag.True,
            UnitAnimationHitStopFlag.False,
            UnitAnimationHoldAtEndFlag.False);

        public static CharacterUnitAnimation WaitJoy { get; } = new(
            UnitAnimationType.WaitJoy,
            "waitjoy",
            Animator.StringToHash("waitjoy"),
            UnitAnimationLoopFlag.True,
            UnitAnimationHitStopFlag.False,
            UnitAnimationHoldAtEndFlag.False);

        public static CharacterUnitAnimation MirrorWaitJoy { get; } = new(
            UnitAnimationType.WaitJoy,
            "waitjoy_mir",
            Animator.StringToHash("waitjoy_mir"),
            UnitAnimationLoopFlag.True,
            UnitAnimationHitStopFlag.False,
            UnitAnimationHoldAtEndFlag.False);

        public static CharacterUnitAnimation Move { get; } = new(
            UnitAnimationType.Move,
            "move",
            Animator.StringToHash("move"),
            UnitAnimationLoopFlag.True,
            UnitAnimationHitStopFlag.False,
            UnitAnimationHoldAtEndFlag.False);

        public static CharacterUnitAnimation MirrorMove { get; } = new(
            UnitAnimationType.Move,
            "move_mir",
            Animator.StringToHash("move_mir"),
            UnitAnimationLoopFlag.True,
            UnitAnimationHitStopFlag.False,
            UnitAnimationHoldAtEndFlag.False);

        public static CharacterUnitAnimation Attack { get; } = new(
            UnitAnimationType.Attack,
            "attack",
            Animator.StringToHash("attack"),
            UnitAnimationLoopFlag.False,
            UnitAnimationHitStopFlag.True,
            UnitAnimationHoldAtEndFlag.False);

        public static CharacterUnitAnimation MirrorAttack { get; } = new(
            UnitAnimationType.Attack,
            "attack_mir",
            Animator.StringToHash("attack_mir"),
            UnitAnimationLoopFlag.False,
            UnitAnimationHitStopFlag.True,
            UnitAnimationHoldAtEndFlag.False);

        public static CharacterUnitAnimation SpecialAttackCharge { get; } = new(
            UnitAnimationType.SpecialAttackCharge,
            "special_attack_charge",
            Animator.StringToHash("special_attack_charge"),
            UnitAnimationLoopFlag.True,
            UnitAnimationHitStopFlag.False,
            UnitAnimationHoldAtEndFlag.False);

        public static CharacterUnitAnimation MirrorSpecialAttackCharge { get; } = new(
            UnitAnimationType.SpecialAttackCharge,
            "special_attack_charge_mir",
            Animator.StringToHash("special_attack_charge_mir"),
            UnitAnimationLoopFlag.True,
            UnitAnimationHitStopFlag.False,
            UnitAnimationHoldAtEndFlag.False);

        public static CharacterUnitAnimation SpecialAttack { get; } = new(
            UnitAnimationType.SpecialAttack,
            "special_attack",
            Animator.StringToHash("special_attack"),
            UnitAnimationLoopFlag.False,
            UnitAnimationHitStopFlag.True,
            UnitAnimationHoldAtEndFlag.False);

        public static CharacterUnitAnimation MirrorSpecialAttack { get; } = new(
            UnitAnimationType.SpecialAttack,
            "special_attack_mir",
            Animator.StringToHash("special_attack_mir"),
            UnitAnimationLoopFlag.False,
            UnitAnimationHitStopFlag.True,
            UnitAnimationHoldAtEndFlag.False);

        public static CharacterUnitAnimation SpecialAttackCutIn { get; } = new(
            UnitAnimationType.SpecialAttackCutIn,
            "special_attack_cutin",
            Animator.StringToHash("special_attack_cutin"),
            UnitAnimationLoopFlag.True,
            UnitAnimationHitStopFlag.False,
            UnitAnimationHoldAtEndFlag.False);

        public static CharacterUnitAnimation Damage { get; } = new(
            UnitAnimationType.Damage,
            "damage",
            Animator.StringToHash("damage"),
            UnitAnimationLoopFlag.False,
            UnitAnimationHitStopFlag.False,
            UnitAnimationHoldAtEndFlag.False);

        public static CharacterUnitAnimation MirrorDamage { get; } = new(
            UnitAnimationType.Damage,
            "damage_mir",
            Animator.StringToHash("damage_mir"),
            UnitAnimationLoopFlag.False,
            UnitAnimationHitStopFlag.False,
            UnitAnimationHoldAtEndFlag.False);

        public static CharacterUnitAnimation KnockBack { get; } = new(
            UnitAnimationType.KnockBack,
            "knock_back",
            Animator.StringToHash("knock_back"),
            UnitAnimationLoopFlag.False,
            UnitAnimationHitStopFlag.False,
            UnitAnimationHoldAtEndFlag.False);

        public static CharacterUnitAnimation MirrorKnockBack { get; } = new(
            UnitAnimationType.KnockBack,
            "knock_back_mir",
            Animator.StringToHash("knock_back_mir"),
            UnitAnimationLoopFlag.False,
            UnitAnimationHitStopFlag.False,
            UnitAnimationHoldAtEndFlag.False);

        public static CharacterUnitAnimation Death { get; } = new(
            UnitAnimationType.Death,
            "death",
            Animator.StringToHash("death"),
            UnitAnimationLoopFlag.False,
            UnitAnimationHitStopFlag.False,
            UnitAnimationHoldAtEndFlag.False);

        public static CharacterUnitAnimation MirrorDeath { get; } = new(
            UnitAnimationType.Death,
            "death_mir",
            Animator.StringToHash("death_mir"),
            UnitAnimationLoopFlag.False,
            UnitAnimationHitStopFlag.False,
            UnitAnimationHoldAtEndFlag.False);

        public static CharacterUnitAnimation Escape { get; } = new(
            UnitAnimationType.Escape,
            "escape",
            Animator.StringToHash("escape"),
            UnitAnimationLoopFlag.False,
            UnitAnimationHitStopFlag.False,
            UnitAnimationHoldAtEndFlag.False);

        public static CharacterUnitAnimation MirrorEscape { get; } = new(
            UnitAnimationType.Escape,
            "escape_mir",
            Animator.StringToHash("escape_mir"),
            UnitAnimationLoopFlag.False,
            UnitAnimationHitStopFlag.False,
            UnitAnimationHoldAtEndFlag.False);

        public static CharacterUnitAnimation Stun { get; } = new(
            UnitAnimationType.Stun,
            "damage",
            Animator.StringToHash("damage"),
            UnitAnimationLoopFlag.False,
            UnitAnimationHitStopFlag.False,
            UnitAnimationHoldAtEndFlag.True);

        public static CharacterUnitAnimation MirrorStun { get; } = new(
            UnitAnimationType.Stun,
            "damage_mir",
            Animator.StringToHash("damage_mir"),
            UnitAnimationLoopFlag.False,
            UnitAnimationHitStopFlag.False,
            UnitAnimationHoldAtEndFlag.True);

        public static CharacterUnitAnimation Freeze { get; } = new(
            UnitAnimationType.Freeze,
            "damage",
            Animator.StringToHash("damage"),
            UnitAnimationLoopFlag.False,
            UnitAnimationHitStopFlag.False,
            UnitAnimationHoldAtEndFlag.True);

        public static CharacterUnitAnimation MirrorFreeze { get; } = new(
            UnitAnimationType.Freeze,
            "damage_mir",
            Animator.StringToHash("damage_mir"),
            UnitAnimationLoopFlag.False,
            UnitAnimationHitStopFlag.False,
            UnitAnimationHoldAtEndFlag.True);

        // 探索宝箱の上から降ってくるモーション用
        public static CharacterUnitAnimation Appearing { get; } = new(
            UnitAnimationType.Appearing,
            "appearing",
            Animator.StringToHash("appearing"),
            UnitAnimationLoopFlag.False,
            UnitAnimationHitStopFlag.False,
            UnitAnimationHoldAtEndFlag.False);

        public override string ToString()
        {
            return Name;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
