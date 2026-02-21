using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record AttackHitData(
        AttackHitType HitType,
        AttackHitParameter HitParameter1,
        AttackHitParameter HitParameter2,
        AttackHitBattleEffectId AttackHitBattleEffectId,
        IReadOnlyList<AttackHitOnomatopoeiaAssetKey> OnomatopoeiaAssetKeys,
        SoundEffectAssetKey SoundEffectAssetKey,
        SoundEffectAssetKey KillerSoundEffectAssetKey,
        AccumulatedDamageKnockBackFlag IsAccumulatedDamageKnockBack)
    {
        static readonly SoundEffectAssetKey DefaultSoundEffectAssetKey = new ("SSE_051_004");
        static readonly SoundEffectAssetKey DefaultKillerSoundEffectAssetKey = new ("SSE_051_013");
        
        public static AttackHitData Empty { get; } = new(
            AttackHitType.Normal,
            AttackHitParameter.Empty,
            AttackHitParameter.Empty,
            AttackHitBattleEffectId.Empty,
            new List<AttackHitOnomatopoeiaAssetKey>(),
            SoundEffectAssetKey.Empty,
            SoundEffectAssetKey.Empty,
            AccumulatedDamageKnockBackFlag.False);

        public static AttackHitData Normal { get; } = new(
            AttackHitType.Normal,
            AttackHitParameter.Empty,
            AttackHitParameter.Empty,
            AttackHitBattleEffectId.Empty,
            new List<AttackHitOnomatopoeiaAssetKey>(),
            DefaultSoundEffectAssetKey,
            DefaultKillerSoundEffectAssetKey,
            AccumulatedDamageKnockBackFlag.True);

        public static AttackHitData NoNockBack { get; } = new(
            AttackHitType.Normal,
            AttackHitParameter.Empty,
            AttackHitParameter.Empty,
            AttackHitBattleEffectId.Empty,
            new List<AttackHitOnomatopoeiaAssetKey>(),
            DefaultSoundEffectAssetKey,
            DefaultKillerSoundEffectAssetKey,
            AccumulatedDamageKnockBackFlag.False);

        public static AttackHitData AccumulatedDamageKnockBack { get; } = new(
            AttackHitType.AccumulatedDamageKnockBack,
            AttackHitParameter.Empty,
            AttackHitParameter.Empty,
            AttackHitBattleEffectId.Empty,
            new List<AttackHitOnomatopoeiaAssetKey>(),
            DefaultSoundEffectAssetKey,
            DefaultKillerSoundEffectAssetKey,
            AccumulatedDamageKnockBackFlag.True);

        public static AttackHitData KnockBack1 { get; } = new(
            AttackHitType.KnockBack1,
            AttackHitParameter.Empty,
            AttackHitParameter.Empty,
            AttackHitBattleEffectId.Empty,
            new List<AttackHitOnomatopoeiaAssetKey>(),
            DefaultSoundEffectAssetKey,
            DefaultKillerSoundEffectAssetKey,
            AccumulatedDamageKnockBackFlag.True);

        public static AttackHitData KnockBack2 { get; } = new(
            AttackHitType.KnockBack2,
            AttackHitParameter.Empty,
            AttackHitParameter.Empty,
            AttackHitBattleEffectId.Empty,
            new List<AttackHitOnomatopoeiaAssetKey>(),
            DefaultSoundEffectAssetKey,
            DefaultKillerSoundEffectAssetKey,
            AccumulatedDamageKnockBackFlag.True);

        public static AttackHitData KnockBack3 { get; } = new(
            AttackHitType.KnockBack3,
            AttackHitParameter.Empty,
            AttackHitParameter.Empty,
            AttackHitBattleEffectId.Empty,
            new List<AttackHitOnomatopoeiaAssetKey>(),
            DefaultSoundEffectAssetKey,
            DefaultKillerSoundEffectAssetKey,
            AccumulatedDamageKnockBackFlag.True);

        public static AttackHitData ForcedKnockBack1 { get; } = new(
            AttackHitType.ForcedKnockBack1,
            AttackHitParameter.Empty,
            AttackHitParameter.Empty,
            AttackHitBattleEffectId.Empty,
            new List<AttackHitOnomatopoeiaAssetKey>(),
            DefaultSoundEffectAssetKey,
            DefaultKillerSoundEffectAssetKey,
            AccumulatedDamageKnockBackFlag.True);

        public static AttackHitData ForcedKnockBack2 { get; } = new(
            AttackHitType.ForcedKnockBack2,
            AttackHitParameter.Empty,
            AttackHitParameter.Empty,
            AttackHitBattleEffectId.Empty,
            new List<AttackHitOnomatopoeiaAssetKey>(),
            DefaultSoundEffectAssetKey,
            DefaultKillerSoundEffectAssetKey,
            AccumulatedDamageKnockBackFlag.True);

        public static AttackHitData ForcedKnockBack3 { get; } = new(
            AttackHitType.ForcedKnockBack3,
            AttackHitParameter.Empty,
            AttackHitParameter.Empty,
            AttackHitBattleEffectId.Empty,
            new List<AttackHitOnomatopoeiaAssetKey>(),
            DefaultSoundEffectAssetKey,
            DefaultKillerSoundEffectAssetKey,
            AccumulatedDamageKnockBackFlag.True);

        public static AttackHitData ForcedKnockBack5 { get; } = new(
            AttackHitType.ForcedKnockBack5,
            AttackHitParameter.Empty,
            AttackHitParameter.Empty,
            AttackHitBattleEffectId.Empty,
            new List<AttackHitOnomatopoeiaAssetKey>(),
            DefaultSoundEffectAssetKey,
            DefaultKillerSoundEffectAssetKey,
            AccumulatedDamageKnockBackFlag.True);

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}