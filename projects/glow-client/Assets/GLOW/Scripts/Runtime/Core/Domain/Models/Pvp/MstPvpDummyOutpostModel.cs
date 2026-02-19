using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;
using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record MstPvpDummyOutpostModel(
        MasterDataId Id,
        PvpDummyUserId MstDummyUserId,
        MasterDataId MstOutpostEnhancementId,
        OutpostEnhanceLevel EnhancementLevel)
    {
        public static MstPvpDummyOutpostModel Empty { get; } = new MstPvpDummyOutpostModel(
            MasterDataId.Empty,
            PvpDummyUserId.Empty,
            MasterDataId.Empty,
            OutpostEnhanceLevel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}