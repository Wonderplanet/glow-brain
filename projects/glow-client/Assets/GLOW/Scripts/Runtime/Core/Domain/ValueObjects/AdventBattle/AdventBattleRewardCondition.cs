using System.Globalization;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.AdventBattle
{
    public record AdventBattleRewardCondition(ObscuredString Value)
    {
        public static AdventBattleRewardCondition Empty { get; } = new(string.Empty);
        
        public static AdventBattleRewardCondition Participation { get; } = new("Participation");
        
        public MasterDataId ToMasterDataId() => new(Value);

        public AdventBattleChallengeCount ToAdventBattleChallengeCount()
        {
            return new AdventBattleChallengeCount(int.Parse(Value, CultureInfo.InvariantCulture));
        }

        public AdventBattleScore ToAdventBattleScore()
        {
            return new AdventBattleScore(long.Parse(Value, CultureInfo.InvariantCulture));
        }

        public AdventBattleRaidTotalScore ToAdventBattleRaidTotalScore()
        {
            return new AdventBattleRaidTotalScore(long.Parse(Value, CultureInfo.InvariantCulture));
        }

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