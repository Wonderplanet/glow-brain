using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstEventBonusUnitModel(
        MasterDataId MstUnitId,
        EventBonusPercentage BonusPercentage,
        EventBonusGroupId EventBonusGroupId,
        PickUpFlag IsPickUp
    )
    {
        public static MstEventBonusUnitModel Empty { get; } = new MstEventBonusUnitModel(
            MasterDataId.Empty,
            EventBonusPercentage.Zero,
            EventBonusGroupId.Empty,
            PickUpFlag.False
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
