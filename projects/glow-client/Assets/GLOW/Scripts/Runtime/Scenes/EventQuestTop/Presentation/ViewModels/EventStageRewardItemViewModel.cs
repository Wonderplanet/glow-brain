using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.EventStageSelect.Presentation.ViewModels
{
    public record EventStageRewardItemViewModel(
        bool IsFirstOnly,
        bool IsGotten,
        ItemIconViewModel ItemIcon
    );
}
