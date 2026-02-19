using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.Extensions
{
    public static class AttackDamageTypeExtension
    {
        public static bool IsHeal(this AttackDamageType attackDamageType)
        {
            return attackDamageType switch
            {
                AttackDamageType.Heal => true,
                _ => false,
            };
        }
    }
}
