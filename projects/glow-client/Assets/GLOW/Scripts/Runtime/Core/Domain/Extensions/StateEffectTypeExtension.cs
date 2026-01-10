using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.Extensions
{
    public static class StateEffectTypeExtension
    {
        public static bool ShouldPlayBuffEffect(this StateEffectType stateEffectType)
        {
            return stateEffectType switch
            {
                StateEffectType.AttackPowerUp or
                StateEffectType.DamageCut or
                StateEffectType.MoveSpeedUp or
                StateEffectType.AttackPowerUpInNormalKoma or
                StateEffectType.MoveSpeedUpInNormalKoma or
                StateEffectType.DamageCutInNormalKoma or
                StateEffectType.RegenerationByFixed or
                StateEffectType.RegenerationByMaxHpPercentage => true,

                _ => false,
            };
        }
        
        public static bool IsUnitConditionBuff(this StateEffectType stateEffectType)
        {
            return stateEffectType switch
            {
                StateEffectType.AttackPowerUpByHpPercentage or
                StateEffectType.DamageCutByHpPercentage => true,

                _ => false,
            };
        }

        public static bool ShouldPlayDebuffEffect(this StateEffectType stateEffectType)
        {
            return stateEffectType switch
            {
                StateEffectType.AttackPowerDown or
                StateEffectType.AttackSpeedDown or
                StateEffectType.MoveSpeedDown => true,

                _ => false,
            };
        }

        public static bool HasNotMulti(this StateEffectType stateEffectType)
        {
            return stateEffectType switch
            {
                StateEffectType.Poison or
                StateEffectType.Burn or
                StateEffectType.Weakening => true,

                _ => false,
            };
        }

        public static bool IsImmediateEffect(this StateEffectType stateEffectType)
        {
            return stateEffectType switch
            {
                StateEffectType.SpecialAttackCoolTimeShorten or
                    StateEffectType.SpecialAttackCoolTimeExtend or
                    StateEffectType.SummonCoolTimeShorten or
                    StateEffectType.SummonCoolTimeExtend => true,

                _ => false,
            };
        }

        public static StateEffectType GetStateEffectTypeThatBlockMe(this StateEffectType stateEffectType)
        {
            // UnitAction変更が発生しない効果の無効化のみ列挙
            // UnitAction変更が発生する効果の無効化はAttackHitTypeExtension.GetStateEffectTypeThatBlockMe参照
            return stateEffectType switch
            {
                StateEffectType.Poison => StateEffectType.PoisonBlock,
                StateEffectType.Weakening => StateEffectType.WeakeningBlock,
                _ => StateEffectType.None
            };
        }

        #region 現状フィルターダイアログのみで使用想定の部分
        public static bool IsStatusUp(this StateEffectType stateEffectType)
        {
            return stateEffectType switch
            {
                StateEffectType.AttackPowerUp or
                StateEffectType.MoveSpeedUp => true,

                _ => false,
            };
        }

        public static bool IsStatusDown(this StateEffectType stateEffectType)
        {
            return stateEffectType switch
            {
                StateEffectType.AttackPowerDown or
                StateEffectType.MoveSpeedDown or
                StateEffectType.AttackSpeedDown => true,

                _ => false
            };
        }

        public static bool IsDamageCut(this StateEffectType stateEffectType)
        {
            return stateEffectType switch
            {
                // DamageCutInNormalKomaに関しては現時点では除外
                StateEffectType.DamageCut  => true,

                _ => false,
            };
        }

        public static bool IsRegeneration(this StateEffectType stateEffectType)
        {
            return stateEffectType switch
            {
                StateEffectType.RegenerationByFixed or
                StateEffectType.RegenerationByMaxHpPercentage => true,

                _ => false,
            };
        }

        #endregion
    }
}
