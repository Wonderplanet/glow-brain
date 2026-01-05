using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.IdleIncentive;

namespace GLOW.Core.Domain.Models
{
    public record MstIdleIncentiveRewardModel(
        MasterDataId MstStageId,
        IdleIncentiveRewardAmount BaseCoinAmount,
        IdleIncentiveRewardAmount BaseExpAmount,
        MasterDataId MstIdleIncentiveItemGroupId
    )
    {
        public static MstIdleIncentiveRewardModel Empty { get; } = new(
            MasterDataId.Empty,
            IdleIncentiveRewardAmount.Empty,
            IdleIncentiveRewardAmount.Empty,
            MasterDataId.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
