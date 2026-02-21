using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Party
{
    public record PartyBonusUnitModel(
        MasterDataId MstUnitId,
        EventBonusPercentage BonusPercentage)
    {
        public static PartyBonusUnitModel Empty { get; } = new PartyBonusUnitModel(
            MasterDataId.Empty,
            EventBonusPercentage.Zero);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
