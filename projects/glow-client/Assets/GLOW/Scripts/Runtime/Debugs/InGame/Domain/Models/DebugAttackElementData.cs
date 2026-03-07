#if GLOW_INGAME_DEBUG
using GLOW.Core.Domain.Constants;

namespace GLOW.Debugs.InGame.Domain.Models
{
    // MstAttackElementDataと同じフィールド構造を持つDebug用データ
    // テーブル定義と一致した形式が良かったので用意
    public record DebugAttackElementData(
        string Id,
        string MstAttackId,
        int SortOrder,
        AttackType AttackType,
        AttackTarget Target,
        AttackTargetType TargetType,
        string TargetColors,
        string TargetRoles,
        string TargetMstSeriesIds,
        string TargetMstCharacterIds,
        AttackRangePointType RangeStartType,
        float RangeStartParameter,
        AttackRangePointType RangeEndType,
        float RangeEndParameter,
        int MaxTargetCount,
        AttackDamageType DamageType,
        AttackHitType HitType,
        int HitParameter1,
        int HitParameter2,
        string HitEffectId,
        bool IsHitStop,
        int Probability,
        AttackPowerParameterType PowerParameterType,
        int PowerParameter,
        StateEffectType EffectType,
        int EffectiveCount,
        int EffectiveDuration,
        float EffectParameter,
        string EffectValue,
        string EffectTriggerRoles,
        string EffectTriggerColors,
        int AttackDelay
    )
    {
        public static DebugAttackElementData Empty { get; } = new(
            string.Empty,
            string.Empty,
            0,
            AttackType.None,
            AttackTarget.Friend,
            AttackTargetType.All,
            "All",
            "All",
            string.Empty,
            string.Empty,
            AttackRangePointType.Distance,
            0,
            AttackRangePointType.Distance,
            0,
            -1,
            AttackDamageType.None,
            AttackHitType.Normal,
            0,
            0,
            string.Empty,
            false,
            0,
            AttackPowerParameterType.Percentage,
            0,
            StateEffectType.None,
            0,
            0,
            0,
            string.Empty,
            string.Empty,
            string.Empty,
            0
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
#endif


