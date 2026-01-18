using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.MessageBox;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Extensions;

namespace GLOW.Core.Domain.Models
{
    public record BadgeModel(
        UnreceivedMissionRewardCount UnreceivedMissionRewardCount,
        UnreceivedMissionRewardCount UnreceivedMissionBeginnerRewardCount,
        IReadOnlyList<MissionEventRewardCountModel> UnreceivedMissionEventRewardCounts,
        UnreceivedMissionRewardCount UnreceivedMissionAdventBattleRewardCount,
        UnopenedMessageCount UnopenedMessageCount)
    {
        public static BadgeModel Empty { get; } = new BadgeModel(
            UnreceivedMissionRewardCount.Empty, 
            UnreceivedMissionRewardCount.Empty, 
            new List<MissionEventRewardCountModel>(),
            UnreceivedMissionRewardCount.Empty,
            UnopenedMessageCount.Empty);

        public UnreceivedMissionRewardCount UnreceivedMissionEventRewardCountById(MasterDataId mstEventId)
        {
            // 指定なしの場合は空を返す
            if (mstEventId.IsEmpty()) return UnreceivedMissionRewardCount.Empty;
            
            return UnreceivedMissionEventRewardCounts
                .FirstOrDefault(
                    model => model.MstEventId == mstEventId, 
                    MissionEventRewardCountModel.Empty)
                .UnreceivedMissionEventRewardCount;
        }
        
        public UnreceivedMissionRewardCount UnreceivedMissionEventRewardTotalCount()
        {
            if (UnreceivedMissionEventRewardCounts.IsEmpty()) return UnreceivedMissionRewardCount.Empty;
            
            var unreceivedMissionRewardCounts = UnreceivedMissionEventRewardCounts
                .Select(model => model.UnreceivedMissionEventRewardCount)
                .Sum(count => count.Value);

            return new UnreceivedMissionRewardCount(unreceivedMissionRewardCounts);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
