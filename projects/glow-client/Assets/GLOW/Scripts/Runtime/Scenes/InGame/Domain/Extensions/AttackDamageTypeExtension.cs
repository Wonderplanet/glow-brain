using System.Collections.Generic;
using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Extensions
{
    public static class AttackDamageTypeExtension
    {
        static readonly IReadOnlyList<StateEffectType> EmptyStateEffectTypes = new List<StateEffectType>();
        
        // AttackDamageTypeに対して、そのダメージをカットするStateEffectType
        static readonly IReadOnlyDictionary<AttackDamageType, IReadOnlyList<StateEffectType>> DamageCutStateEffectDictionary = 
            new Dictionary<AttackDamageType, IReadOnlyList<StateEffectType>>
        {
            { AttackDamageType.Damage, new List<StateEffectType>
            {
                StateEffectType.DamageCut,
                StateEffectType.DamageCutInNormalKoma,
                StateEffectType.DamageCutByHpPercentage
            } },
            
            { AttackDamageType.PoisonDamage, new List<StateEffectType> { StateEffectType.PoisonDamageCut } },
            { AttackDamageType.BurnDamage, new List<StateEffectType> { StateEffectType.BurnDamageCut } }
        };
        
        public static IReadOnlyList<StateEffectType> GetStateEffectTypesThatCutMe(this AttackDamageType attackDamageType)
        {
            if (DamageCutStateEffectDictionary.TryGetValue(attackDamageType, out var stateEffectTypes))
            {
                return stateEffectTypes;
            }
            
            return EmptyStateEffectTypes;
        }
        
        public static bool IsDamage(this AttackDamageType attackDamageType)
        {
            return attackDamageType is 
                AttackDamageType.Damage or 
                AttackDamageType.RushDamage or 
                AttackDamageType.BurnDamage or 
                AttackDamageType.PoisonDamage or 
                AttackDamageType.SlipDamage;
        }
        
        public static bool NeedPlayDamageMotion(this AttackDamageType attackDamageType)
        {
            return attackDamageType is
                AttackDamageType.Damage or
                AttackDamageType.RushDamage;
        }
    }
}
