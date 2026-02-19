using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstItemTransitionModel(
        MasterDataId MstItemId,
        ItemTransitionType TransitionType1,
        MasterDataId TransitionMasterDataId1,
        ItemTransitionType TransitionType2,
        MasterDataId TransitionMasterDataId2
    )
    {
        public static MstItemTransitionModel Empty = new MstItemTransitionModel(
            MasterDataId.Empty,
            ItemTransitionType.None,
            MasterDataId.Empty,
            ItemTransitionType.None,
            MasterDataId.Empty
        );
    }
}
