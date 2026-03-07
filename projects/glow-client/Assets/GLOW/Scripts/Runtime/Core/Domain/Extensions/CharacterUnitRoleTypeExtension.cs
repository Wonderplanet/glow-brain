using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.Extensions
{
    public static class CharacterUnitRoleTypeExtension
    {
        public static BattleEffectId GetSpecialAttackAuraEffectId(this CharacterUnitRoleType roleType)
        {
            return roleType switch
            {
                CharacterUnitRoleType.Attack => BattleEffectId.SpecialAttackAura_attackRole,
                CharacterUnitRoleType.Balance => BattleEffectId.SpecialAttackAura_balanceRole,
                CharacterUnitRoleType.Defense => BattleEffectId.SpecialAttackAura_defenseRole,
                CharacterUnitRoleType.Support => BattleEffectId.SpecialAttackAura_supportRole,
                CharacterUnitRoleType.Unique => BattleEffectId.SpecialAttackAura_uniqueRole,
                CharacterUnitRoleType.Technical => BattleEffectId.SpecialAttackAura_technicalRole,
                CharacterUnitRoleType.Special => BattleEffectId.SpecialAttackAura_specialRole,
                _ => BattleEffectId.None
            };
        }
        
        public static bool IsSummonableOnField(this CharacterUnitRoleType roleType)
        {
            return roleType != CharacterUnitRoleType.None && roleType != CharacterUnitRoleType.Special;
        }
    }
}
