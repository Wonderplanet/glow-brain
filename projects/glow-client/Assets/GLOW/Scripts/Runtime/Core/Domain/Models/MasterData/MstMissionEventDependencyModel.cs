using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record MstMissionEventDependencyModel(
        MasterDataId Id,
        MasterDataId GroupId,
        MasterDataId MstMissionEventId,
        UnlockOrder UnlockOrder);
}