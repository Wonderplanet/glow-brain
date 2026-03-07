using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record UserUnitModel(
        MasterDataId MstUnitId,
        UserDataId UsrUnitId,
        UnitLevel Level,
        UnitRank Rank,
        UnitGrade Grade,
        NewEncyclopediaFlag IsNewEncyclopedia,
        UnitGrade LastRewardGrade)
    {
        public static UserUnitModel Empty { get; } = new(
            MasterDataId.Empty,
            UserDataId.Empty,
            UnitLevel.Empty,
            UnitRank.Empty,
            UnitGrade.Empty,
            NewEncyclopediaFlag.False,
            UnitGrade.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
