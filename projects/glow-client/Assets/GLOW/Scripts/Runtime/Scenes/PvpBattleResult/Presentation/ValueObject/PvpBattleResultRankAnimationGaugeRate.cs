using UnityEngine;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.PvpBattleResult.Presentation.ValueObject
{
    public record PvpBattleResultRankAnimationGaugeRate(ObscuredFloat Value)
    {
        public static PvpBattleResultRankAnimationGaugeRate Empty { get; } = new PvpBattleResultRankAnimationGaugeRate(0.0f);
        
        public static PvpBattleResultRankAnimationGaugeRate Zero { get; } = new PvpBattleResultRankAnimationGaugeRate(0.0f);
        
        public static PvpBattleResultRankAnimationGaugeRate One { get; } = new PvpBattleResultRankAnimationGaugeRate(1.0f);
        
        public static bool operator >(PvpBattleResultRankAnimationGaugeRate a, PvpBattleResultRankAnimationGaugeRate b)
        {
            return a.Value > b.Value;
        }
        
        public static bool operator <(PvpBattleResultRankAnimationGaugeRate a, PvpBattleResultRankAnimationGaugeRate b)
        {
            return a.Value < b.Value;
        }
        
        public ObscuredFloat Value { get; } = Mathf.Clamp01(Value);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        } 
    }
}