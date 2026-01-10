using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Data.Translators.StaminaRecover;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.MessageBox;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Data.Translators
{
    public class BadgeDataTranslator
    {
        public static BadgeModel ToBadgeModel(BadgeData badgeData)
        {
            return new BadgeModel(
                new UnreceivedMissionRewardCount(badgeData.UnreceivedMissionRewardCount),
                new UnreceivedMissionRewardCount(badgeData.UnreceivedMissionBeginnerRewardCount),
                badgeData.UnreceivedMissionEventRewardCounts
                    .Select(MissionEventRewardCountDataTranslator.ToModel)
                    .ToList(),
                new UnreceivedMissionRewardCount(badgeData.UnreceivedMissionAdventBattleRewardCount),
                new UnopenedMessageCount(badgeData.UnopenedMessageCount)
            );
        }
    }
}