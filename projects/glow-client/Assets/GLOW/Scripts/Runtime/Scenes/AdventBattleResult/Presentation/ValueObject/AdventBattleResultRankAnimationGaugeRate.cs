using UnityEngine;

namespace GLOW.Scenes.AdventBattleResult.Presentation.ValueObject
{
    public record AdventBattleResultRankAnimationGaugeRate(float Value)
    {
        public static AdventBattleResultRankAnimationGaugeRate Empty { get; } = new AdventBattleResultRankAnimationGaugeRate(0.0f);
        
        public static AdventBattleResultRankAnimationGaugeRate Zero { get; } = new AdventBattleResultRankAnimationGaugeRate(0.0f);
        
        public static AdventBattleResultRankAnimationGaugeRate One { get; } = new AdventBattleResultRankAnimationGaugeRate(1.0f);
        
        public float Value { get; } = Mathf.Clamp01(Value);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}