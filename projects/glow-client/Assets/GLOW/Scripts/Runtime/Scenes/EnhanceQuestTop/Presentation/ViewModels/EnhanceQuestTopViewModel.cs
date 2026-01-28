using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.EnhanceQuestTop.Presentation.Views
{
    public record EnhanceQuestTopViewModel(
        EnhanceQuestScore HighScore,
        EnhanceQuestMinThresholdScore NextThresholdScore,
        ItemAmount NextThresholdRewardAmount,
        EnhanceQuestChallengeCount ChallengeCount,
        EnhanceQuestChallengeCount AdChallengeCount,
        EventBonusPercentage TotalBonusPercentage,
        PartyName PartyName,
        HeldAdSkipPassInfoViewModel HeldAdSkipPassInfoViewModel,
        List<CampaignViewModel> CampaignViewModels);
}
