using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record MstMissionLimitedTermDependencyModel(
        MasterDataId Id,
        MasterDataId GroupId,
        MasterDataId MstMissionLimitedTermId,
        UnlockOrder UnlockOrder);

}