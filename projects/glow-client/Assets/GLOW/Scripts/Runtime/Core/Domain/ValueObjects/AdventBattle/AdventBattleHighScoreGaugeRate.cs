using UnityEngine;

namespace GLOW.Core.Domain.ValueObjects.AdventBattle
{
    public record AdventBattleHighScoreGaugeRate(float Value)
    {
        public float Value { get; } = Mathf.Clamp01(Value);
        
        public static AdventBattleHighScoreGaugeRate Empty { get; } = new AdventBattleHighScoreGaugeRate(0.0f);
        
        public static AdventBattleHighScoreGaugeRate Zero { get; } = new AdventBattleHighScoreGaugeRate(0.0f);
        
        public static AdventBattleHighScoreGaugeRate One { get; } = new AdventBattleHighScoreGaugeRate(1.0f);
        
        public static AdventBattleHighScoreGaugeRate operator +(AdventBattleHighScoreGaugeRate a, AdventBattleHighScoreGaugeRate b)
        {
            return new AdventBattleHighScoreGaugeRate(a.Value + b.Value);
        }
        
        public static AdventBattleHighScoreGaugeRate operator -(AdventBattleHighScoreGaugeRate a, AdventBattleHighScoreGaugeRate b)
        {
            return new AdventBattleHighScoreGaugeRate(a.Value - b.Value);
        }
        
        public static AdventBattleHighScoreGaugeRate operator *(AdventBattleHighScoreGaugeRate a, AdventBattleHighScoreGaugeRate b)
        {
            return new AdventBattleHighScoreGaugeRate(a.Value * b.Value);
        }
        
        public static AdventBattleHighScoreGaugeRate operator *(AdventBattleHighScoreGaugeRate a, int b)
        {
            return new AdventBattleHighScoreGaugeRate(a.Value * b);
        }
        
        public static AdventBattleHighScoreGaugeRate operator *(AdventBattleHighScoreGaugeRate a, float b)
        {
            return new AdventBattleHighScoreGaugeRate(a.Value * b);
        }
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}