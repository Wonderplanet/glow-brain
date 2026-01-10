using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pvp
{
    public record PvpRewardConditionValue(ObscuredString Value)
    {
        public static PvpRewardConditionValue Empty { get; } = new PvpRewardConditionValue(string.Empty);
        
        public MasterDataId ToMasterDataId()
        {
            return new MasterDataId(Value);
        }
        
        public PvpRankingRank ToPvpRankingRank()
        {
            return new PvpRankingRank(int.Parse(Value));
        }
        
        public PvpPoint ToPvpPoint()
        {
            return new PvpPoint(long.Parse(Value));
        }

        public override string ToString()
        {
            return Value.ToString();
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}