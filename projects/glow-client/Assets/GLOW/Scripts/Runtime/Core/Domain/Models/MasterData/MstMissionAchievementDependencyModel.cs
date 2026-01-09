using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record MstMissionAchievementDependencyModel(
        MasterDataId Id,
        MasterDataId GroupId,
        MasterDataId MstMissionAchievementId,
        UnlockOrder UnlockOrder
    );
}