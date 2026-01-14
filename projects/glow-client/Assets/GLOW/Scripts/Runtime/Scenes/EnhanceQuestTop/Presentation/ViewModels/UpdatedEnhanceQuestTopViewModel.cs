using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EnhanceQuestTop.Presentation.ViewModels
{
    public record UpdatedEnhanceQuestTopViewModel(
        PartyName PartyName,
        EventBonusPercentage TotalBonusPercentage
    );
}

