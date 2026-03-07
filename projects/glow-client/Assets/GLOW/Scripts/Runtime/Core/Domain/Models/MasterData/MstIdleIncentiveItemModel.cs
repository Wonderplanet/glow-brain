using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.IdleIncentive;

namespace GLOW.Core.Domain.Models
{
    public record MstIdleIncentiveItemModel(
        MasterDataId Id,
        MasterDataId MstIdleIncentiveItemGroupId,
        MasterDataId MstItemId,
        IdleIncentiveRewardAmount BaseAmount
    );
}
