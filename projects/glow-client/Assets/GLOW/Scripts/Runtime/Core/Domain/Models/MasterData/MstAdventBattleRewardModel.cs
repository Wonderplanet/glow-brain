using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstAdventBattleRewardModel(
        MasterDataId Id,
        MasterDataId MstAdventBattleRewardGroupId,
        ResourceType ResourceType,
        MasterDataId ResourceId,
        ObscuredPlayerResourceAmount ResourceAmount);
}