using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;

namespace GLOW.Core.Domain.Models
{
    public record UserShopPassModel(
        MasterDataId MstShopPassId,
        DailyRewardReceivedCount DailyRewardReceivedCount,
        DailyLatestReceivedPassAt DailyLatestReceivedAt,
        PassStartAt StartAt,
        PassEndAt EndAt)
    {
        public static UserShopPassModel Empty { get; } = new(
            MasterDataId.Empty,
            DailyRewardReceivedCount.Empty,
            DailyLatestReceivedPassAt.Empty,
            PassStartAt.Empty,
            PassEndAt.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}