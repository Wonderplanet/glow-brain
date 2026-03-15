using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Gacha;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.ValueObjects;

namespace GLOW.Core.Domain.ModelFactories
{
    public interface IPlayerResourceModelFactory
    {
        PlayerResourceModel Create(ResourceType type, MasterDataId id, PlayerResourceAmount amount);
        PlayerResourceModel Create(ResourceType type, MasterDataId id, PlayerResourceAmount amount, RewardCategory rewardCategory);
        PlayerResourceModel Create(ResourceType type, MasterDataId id, PlayerResourceAmount amount, RewardCategory rewardCategory, AcquiredFlag acquiredFlag);
        PlayerResourceModel Create(ResourceType type, MasterDataId id, PlayerResourceAmount amount, RewardCategory rewardCategory, AcquiredFlag acquiredFlag, StageClearTime clearTime);
        PlayerResourceModel Create(PreConversionResourceModel model);
    }
}
