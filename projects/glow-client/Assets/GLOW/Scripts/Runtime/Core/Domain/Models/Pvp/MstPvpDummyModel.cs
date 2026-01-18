using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record MstPvpDummyModel(
        MasterDataId Id,
        PvpDummyUserId MstDummyUserId,
        PvpPoint CurrentUserPoint)
    {
        public static MstPvpDummyModel Empty { get; } = new MstPvpDummyModel(
            MasterDataId.Empty,
            PvpDummyUserId.Empty,
            PvpPoint.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}