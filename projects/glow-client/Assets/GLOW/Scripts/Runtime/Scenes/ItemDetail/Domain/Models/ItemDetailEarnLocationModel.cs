using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.StaminaRecover;

namespace GLOW.Scenes.ItemDetail.Domain.Models
{
    public record ItemDetailEarnLocationModel(
        ItemTransitionType TransitionType,
        MasterDataId MasterDataId,
        TransitionPossibleFlag TransitionPossibleFlag
    )
    {
        public static ItemDetailEarnLocationModel Empty { get; } = new ItemDetailEarnLocationModel(
            ItemTransitionType.None,
            MasterDataId.Empty,
            TransitionPossibleFlag.False
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

    }
}
