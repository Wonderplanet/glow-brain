using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.AdventBattle
{
    public record AdventBattleRewardCondition(ObscuredString Value)
    {
        public static AdventBattleRewardCondition Empty { get; } = new(string.Empty);
        
        public static AdventBattleRewardCondition Participation { get; } = new("Participation");
        
        public MasterDataId ToMasterDataId() => new(Value);
        
        public AdventBattleChallengeCount ToAdventBattleChallengeCount() => new(int.Parse(Value));
        
        public AdventBattleScore ToAdventBattleScore() => new(int.Parse(Value));
        
        public AdventBattleRaidTotalScore ToAdventBattleRaidTotalScore() => new(long.Parse(Value));

        public AdventBattleRankingRank ToRankingRank()
        {
            if (IsParticipation())
            {
                return AdventBattleRankingRank.Infinity;
            }
            
            return new AdventBattleRankingRank(int.Parse(Value));
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        bool IsParticipation()
        {
            return Value == Participation.Value;
        }
    }
}