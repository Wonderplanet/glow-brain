using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record PvpUnitModel(
        MasterDataId MstUnitId,
        UnitLevel UnitLevel,
        UnitRank UnitRank,
        UnitGrade UnitGrade
    )
    {
        public static PvpUnitModel Empty { get; } = new(
            MasterDataId.Empty,
            UnitLevel.Empty,
            UnitRank.Empty,
            UnitGrade.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
