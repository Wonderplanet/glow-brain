using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record MstPvpDummyUserUnitModel(
        MasterDataId Id,
        PvpDummyUserId MstDummyUserId,
        MasterDataId MstUnitId,
        UnitLevel UnitLevel,
        UnitRank UnitRank,
        UnitGrade GradeLevel)
    {
        public static MstPvpDummyUserUnitModel Empty { get; } = new MstPvpDummyUserUnitModel(
            MasterDataId.Empty,
            PvpDummyUserId.Empty,
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