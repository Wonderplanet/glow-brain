using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record UserMissionBonusPointModel(
        MissionType MissionType, 
        BonusPoint Point, 
        IReadOnlyList<BonusPoint> ReceivedRewardPoints)
    {
        public static UserMissionBonusPointModel Empty { get; } = new UserMissionBonusPointModel(
            MissionType.Achievement,
            BonusPoint.Empty,
            new List<BonusPoint>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}