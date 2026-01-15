using System.Collections.Generic;
using GLOW.Core.Domain.Models.Campaign;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.PassShop.Domain.Model;

namespace GLOW.Scenes.EnhanceQuestTop.Domain.Models
{
    public record EnhanceQuestTopModel(
        MasterDataId MstStageId,
        MasterDataId MstQuestId,
        EnhanceQuestScore HighScore,
        EnhanceQuestMinThresholdScore NextThresholdScore,
        ItemAmount NextThresholdRewardAmount,
        EnhanceQuestChallengeCount ChallengeCount,
        EnhanceQuestChallengeCount AdChallengeCount,
        EventBonusPercentage TotalBonusPercentage,
        PartyName PartyName,
        EventBonusGroupId EventBonusGroupId,
        HeldAdSkipPassInfoModel HeldAdSkipPassInfoModel,
        List<CampaignModel> CampaignModels);
}
