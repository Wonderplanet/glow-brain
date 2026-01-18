using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.Extensions
{
    public static class UnitAuraTypeExtension
    {
        public static BattleEffectId ToBattleEffectId(this UnitAuraType auraType)
        {
            return auraType switch
            {
                UnitAuraType.Boss => BattleEffectId.BossAura,
                UnitAuraType.AdventBoss1 => BattleEffectId.AdventBossAura1,
                UnitAuraType.AdventBoss2 => BattleEffectId.AdventBossAura2,
                UnitAuraType.AdventBoss3 => BattleEffectId.AdventBossAura3,
                _ => BattleEffectId.None
            };
        }
    }
}