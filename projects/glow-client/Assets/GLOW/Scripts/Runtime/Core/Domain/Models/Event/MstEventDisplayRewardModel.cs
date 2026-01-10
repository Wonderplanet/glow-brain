using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Event
{
    public record MstEventDisplayRewardModel(
        MasterDataId Id,
        MasterDataId MstEventId,
        ResourceType ResourceType,
        MasterDataId ResourceId,
        SortOrder SortOrder)
    {
        public static MstEventDisplayRewardModel Empty { get; } = new MstEventDisplayRewardModel(
            MasterDataId.Empty,
            MasterDataId.Empty,
            ResourceType.Item,
            MasterDataId.Empty,
            SortOrder.Empty
        );
        public bool IsEmpty() => ReferenceEquals(this, Empty);
    };
}
