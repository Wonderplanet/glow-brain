using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record MstPvpDummyUserModel(
        MasterDataId Id,
        EncyclopediaUnitGrade EncyclopediaUnitGrade,
        MasterDataId MstEmblemId,
        MasterDataId MstUnitId,
        PvpUserName UserName)
    {
        public static MstPvpDummyUserModel Empty { get; } = new MstPvpDummyUserModel(
            MasterDataId.Empty,
            EncyclopediaUnitGrade.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            PvpUserName.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}