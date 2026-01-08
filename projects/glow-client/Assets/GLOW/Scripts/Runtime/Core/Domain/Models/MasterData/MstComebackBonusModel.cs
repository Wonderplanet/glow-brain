using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record MstComebackBonusModel(
        MasterDataId Id,
        MasterDataId MstComebackBonusScheduleId,
        LoginDayCount LoginDayCount,
        MasterDataId MstMissionRewardGroupId,
        SortOrder SortOrder)
    {
        public static MstComebackBonusModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            LoginDayCount.Empty,
            MasterDataId.Empty,
            SortOrder.Empty
        );
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}